<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\services\SubscriptionService;
use app\repositories\AuthorRepository;
use app\repositories\BookRepository;
use yii\filters\AccessControl;
use app\models\LoginForm;

/**
 * Основной контроллер сайта (главная, логин, подписка, отчёт)
 */
class SiteController extends Controller
{
    private SubscriptionService $subscriptionService;
    private AuthorRepository $authorRepo;

    public function __construct(
        $id,
        $module,
        SubscriptionService $subscriptionService,
        AuthorRepository $authorRepo,
        $config = []
    ) {
        $this->subscriptionService = $subscriptionService;
        $this->authorRepo = $authorRepo;
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
                        'actions' => ['login', 'error', 'subscribe', 'top-authors'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'subscriptions'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Главная страница (перенаправляет на список книг)
     *
     * @return \yii\web\Response
     */
    public function actionIndex(): \yii\web\Response
    {
        return $this->redirect(['book/index']);
    }

    /**
     * Вход в систему
     *
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', ['model' => $model]);
    }

    /**
     * Выход из системы
     *
     * @return \yii\web\Response
     */
    public function actionLogout(): \yii\web\Response
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Страница подписки на уведомления
     *
     * @return string|\yii\web\Response
     */
    public function actionSubscribe()
    {
        $authors = $this->authorRepo->findAll();
        if (Yii::$app->request->isPost) {
            $authorId = (int)Yii::$app->request->post('author_id');
            $phone = Yii::$app->request->post('phone');
            if ($this->subscriptionService->subscribe($authorId, $phone)) {
                Yii::$app->session->setFlash('success', 'Вы успешно подписались.');
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка подписки.');
            }
            return $this->refresh();
        }
        return $this->render('subscribe', ['authors' => $authors]);
    }

    /**
     * Отчёт ТОП-10 авторов за указанный год
     *
     * @param int|null $year
     * @return string
     */
    public function actionTopAuthors(?int $year = null): string
    {
        $year = $year ?? (date('Y') - 1);
        $top = $this->authorRepo->getTopAuthorsByYear($year, 10);
        return $this->render('top-authors', ['top' => $top, 'year' => $year]);
    }

    /**
     * Обработчик ошибок
     *
     * @return string
     */
    public function actionError(): string
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
        return '';
    }

    /**
     * Просмотр всех подписок (доступно только авторизованным)
     *
     * @return string
     */
    public function actionSubscriptions(): string
    {
        $subscriptions = \app\models\Subscription::find()
            ->with('author')
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('subscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
