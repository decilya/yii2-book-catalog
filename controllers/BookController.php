<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Book;
use app\models\Author;
use app\services\BookService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

/**
 * Контроллер управления книгами
 */
class BookController extends Controller
{
    private BookService $bookService;

    public function __construct($id, $module, BookService $bookService, $config = [])
    {
        $this->bookService = $bookService;
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $books = $this->bookService->getAll();
        return $this->render('index', ['books' => $books]);
    }

    public function actionView(int $id): string
    {
        $book = $this->bookService->getOne($id);
        if (!$book) {
            throw new NotFoundHttpException('Книга не найдена.');
        }
        return $this->render('view', ['book' => $book]);
    }

    public function actionCreate()
    {
        $model = new Book();
        $authors = Author::find()->all();

        if ($model->load(Yii::$app->request->post())) {
            // Логируем полученные данные
            Yii::info('POST Book: ' . json_encode(Yii::$app->request->post('Book'), JSON_UNESCAPED_UNICODE), 'book');

            $photo = UploadedFile::getInstance($model, 'photo');
            $authorIds = Yii::$app->request->post('Book')['author_ids'] ?? [];

            Yii::info('authorIds: ' . json_encode($authorIds), 'book');

            if ($this->bookService->createBook($model, $authorIds, $photo)) {
                Yii::$app->session->setFlash('success', 'Книга добавлена. Подписчики уведомлены.');
                return $this->redirect(['index']);
            } else {
                $errors = $model->getErrors();
                Yii::error('Ошибки модели: ' . json_encode($errors, JSON_UNESCAPED_UNICODE), 'book');
                Yii::$app->session->setFlash('error', 'Ошибка сохранения: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'authors' => $authors,
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->bookService->getOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Книга не найдена.');
        }
        $authors = Author::find()->all();
        $model->author_ids = ArrayHelper::getColumn($model->authors, 'id');

        if ($model->load(Yii::$app->request->post())) {
            $photo = UploadedFile::getInstance($model, 'photo');
            $authorIds = Yii::$app->request->post('Book')['author_ids'] ?? [];
            if ($this->bookService->updateBook($model, $authorIds, $photo)) {
                Yii::$app->session->setFlash('success', 'Книга обновлена.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $errors = $model->getErrors();
                Yii::$app->session->setFlash('error', 'Ошибка обновления: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            }
        }

        return $this->render('update', [
            'model' => $model,
            'authors' => $authors,
        ]);
    }

    public function actionDelete(int $id): \yii\web\Response
    {
        if ($this->bookService->deleteBook($id)) {
            Yii::$app->session->setFlash('success', 'Книга удалена.');
        }
        return $this->redirect(['index']);
    }
}
