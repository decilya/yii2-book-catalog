<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\Author;
use app\services\AuthorService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

/**
 * Контроллер управления авторами (CRUD)
 */
class AuthorController extends Controller
{
    private AuthorService $authorService;

    public function __construct($id, $module, AuthorService $authorService, $config = [])
    {
        $this->authorService = $authorService;
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

    /**
     * Список авторов
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $authors = $this->authorService->findAll();
        return $this->render('index', ['authors' => $authors]);
    }

    /**
     * Просмотр одного автора
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        $author = $this->authorService->findById($id);
        if (!$author) {
            throw new NotFoundHttpException('Автор не найден.');
        }
        return $this->render('view', ['author' => $author]);
    }

    /**
     * Создание нового автора
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Author();
        if ($model->load(Yii::$app->request->post()) && $this->authorService->create($model)) {
            Yii::$app->session->setFlash('success', 'Автор добавлен.');
            return $this->redirect(['index']);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Редактирование автора
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        $model = $this->authorService->findById($id);
        if (!$model) {
            throw new NotFoundHttpException('Автор не найден.');
        }
        if ($model->load(Yii::$app->request->post()) && $this->authorService->update($model)) {
            Yii::$app->session->setFlash('success', 'Автор обновлён.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * Удаление автора
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete(int $id): \yii\web\Response
    {
        if ($this->authorService->delete($id)) {
            Yii::$app->session->setFlash('success', 'Автор удалён.');
        }
        return $this->redirect(['index']);
    }
}
