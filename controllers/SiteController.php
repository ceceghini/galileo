<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        $dataProviderMessage = new ActiveDataProvider([
          'query' => \app\models\Message::find(),
          'sort' => false
        ]);

        $data = array();

        $this->executeQuery($data, "select '[verdestampa] PRODOTTI DA ELABORARE' as TITLE, count(*) as N from toner_product where elaborato = 0 and manuale = 0", true);
        //$this->executeQuery($data, "select '[verdestampa] PRODOTTI ELABORATI' as TITLE, count(*) as N from toner_product where elaborato = 1");
        $this->executeQuery($data, "select '[verdestampa] PRODOTTI NON PRESENTI' as TITLE, count(*) as N from toner_product where id_verdestampa = 0 and manuale = 0", true);

        $this->executeQuery($data, "select '[verdestampa] MODELLI DA ELABORARE' as TITLE, count(*) as N from toner_modelli where elaborato = 0 and id_verdestampa > 0", true);
        //$this->executeQuery($data, "select '[verdestampa] MODELLI ELABORATI' as TITLE, count(*) as N from toner_modelli where elaborato = 1");
        //$this->executeQuery($data, "select '[verdestampa] MODELLI NON PIÙ PRESENTI' as TITLE, count(*) as N from toner_modelli where id_verdestampa = 0", true);

        //$this->executeQuery($data, "select '[verdestampa] SERIE NON PIÙ PRESENTI' as TITLE, count(*) as N from toner_serie where id_verdestampa = 0", true);

        //$data[] = null;

        $this->executeQuery($data, "select concat('[',source, '] ', 'PRODOTTI DA ELABORARE') as TITLE, count(*) as N from toner_source_product where elaborato = 0 group by source", true);
        //$this->executeQuery($data, "select concat('[',source, '] ', 'PRODOTTI ELABORATI') as TITLE, count(*) as N from toner_source_product where elaborato = 1 group by source");
        $this->executeQuery($data, "select concat('[',source, '] ', 'PRODOTTI NON PRESENTI') as TITLE, count(*) as N from toner_source_product where is_present = 0 group by source", true);

        $this->executeQuery($data, "select concat('[',source, '] ', 'MODELLI DA ELABORARE') as TITLE, count(*) as N from toner_source_modelli where elaborato = 0 group by source", true);
        //$this->executeQuery($data, "select concat('[',source, '] ', 'MODELLI ELABORATI') as TITLE, count(*) as N from toner_source_modelli where elaborato = 1 group by source");
        $this->executeQuery($data, "select concat('[',source, '] ', 'MODELLI NON PIÙ PRESENTI') as TITLE, count(*) as N from toner_source_modelli where is_present = 0 group by source", true);

        $this->executeQuery($data, "select concat('[',source, '] ', 'URL DA ELABORARE') as TITLE, count(*) as N from toner_source_url where elaborato = 0 group by source", true);
        //$this->executeQuery($data, "select concat('[',source, '] ', 'MODELLI ELABORATI') as TITLE, count(*) as N from toner_source_modelli where elaborato = 1 group by source");
        $this->executeQuery($data, "select concat('[',source, '] ', 'URL NON PIÙ PRESENTI') as TITLE, count(*) as N from toner_source_url where is_present = 0 group by source", true);

        $this->executeQuery($data, "select concat('[',source, '] ', 'SERIE NON PIÙ PRESENTI') as TITLE, count(*) as N from toner_source_serie where is_present = 0 group by source", true);

        $dataProviderVS = new ArrayDataProvider([
          'allModels' => $data,
          'sort' => false
        ]);

        return $this->render('index', [
            'dataProviderVS' => $dataProviderVS,
            'dataProviderMessage' => $dataProviderMessage,
//            'dataProviderFile' => $dataProviderFile
        ]);
    }

    private function executeQuery(&$data, $sql, $alert = false) {

      $results = \Yii::$app->db->createCommand($sql)
        ->queryAll();

      if (!$results)
        return;

      foreach ($results as $result) {

        $result["ALERT"] = "";

        if ($result["N"]>0 && $alert)
          $result["ALERT"] = "!!!";

        if ($result["N"]==0 && $alert)
          return;

        $data[] = $result;

      }

    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    /*public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }*/

    /**
     * Logout action.
     *
     * @return Response
     */
    /*public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }*/

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    /*public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }*/

    /**
     * Displays about page.
     *
     * @return string
     */
    /*public function actionAbout()
    {
        return $this->render('about');
    }*/
}
