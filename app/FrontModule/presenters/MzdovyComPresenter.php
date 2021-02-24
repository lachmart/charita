<?php

/**
 *
 *
 */

namespace FrontModule;

use App\Components\IMzdovyParControlFactory;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Repository\MzdovyComRepository;
use App\Forms\IMzdovyComFormFactory;
use App\Forms\MzdovyComForm;
use App\Components\IMzdovyComControlFactory;
use App\Components\IWRControlFactory;
use App\Model\Repository\WRRepository;
use App\Model\Repository\ZamestnanecRepository;
use Mpdf\Mpdf as mPDF;
use Nette\Application\UI\ITemplateFactory;
use App\Components\IRozpadstrediskaControlFactory;
//define("ALL", -1);

class MzdovyComPresenter extends BasePresenter
{
    /** @var MzdovyComRepository */
    private $mzdovyComRepository;

    /** @var WRRepository */
    private $wRRepository;

    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;

    /**
     * @var IMzdovyComControlFactory
     * @inject
     */
    public $mzdovyComControlFactory;

    /** @var \IMzdovyComFormFactory */
    private $mzdovyComFormFactory;

    public $zamestnanecControlFactory;
    /**
     * @var IWRControlFactory
     * @inject
     */
    public $wRControlFactory;
    /**
     * @var IMzdovyParControlFactory
     * @inject
     */
    public $mzdovyParControlFactory;

    /** @var ITemplateFactory @inject */
    public $templateFactory;

    /** @persistent */
    public $mzdovyComId;
    /** @persistent */
    public $zamestnanecId;
    /** @persistent */
    public $pomerId;

    /**
     * @var IRozpadstrediskaControlFactory
     * @inject
     */
    public $rozpadstrediskaControlFactory;

    public function __construct(MzdovyComRepository $mzdovyComRepository,WRRepository $wRRepository,ZamestnanecRepository $zamestnanecRepository,
                                MzdovyComForm $mzdovyComFormFactory)
    {
        parent::__construct();
        $this->mzdovyComRepository = $mzdovyComRepository;
        $this->wRRepository = $wRRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->mzdovyComFormFactory =  $mzdovyComFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->mzdovyComTypeFilterId = ALL;

    }


    function renderDefault()
    {
      $this->zamestnanecId = NULL;
      $this->pomerId = NULL;
        //$this->template->mzdovyComTypes = $this->mzdovyComRepository->getAllMzdovyComTypes();

    }


    function actionCreate($zamestnanecId)
    {
        $wRs = $this->wRRepository->getWRsWithoutMzdovyCom($zamestnanecId);
      if(count($wRs)){
        $mzdovyComId = $this->mzdovyComRepository->makeMzdovyCom($zamestnanecId);

       foreach ($wRs as $wR) {
          $this->flashMessage('Objednávka '.$wR->pomerId.' byla přidána k faktuře.', 'alert-success');
          $this->wRRepository->addMzdovyComId($wR->pomerId, $mzdovyComId);
        }
          $total = $this->wRRepository->getTotalMzdovyCom($mzdovyComId);
          $data['total']=$total;
          $data['created_by']=$this->user->getId();
          $mzdovyCom = $this->mzdovyComRepository->getMzdovyCom($mzdovyComId);
          $mzdovyCom->update($data);
        $this->flashMessage('Faktura za '.$total .' kč byla vytvořena a změny uloženy.', 'alert-success');
      }
      else
        $this->flashMessage('Nebyly nalezeny fakturovatelné objednávky.', 'alert-danger');
      $this->redirect('Zamestnanec:');
       // $this->template->mzdovyComAttributes = $this->mzdovyComRepository->getAllMzdovyComAttributesForMzdovyComType($mzdovyComTypeId);
        //$this->zamestnanecId = $zamestnanecId;
        //$this['mzdovyComForm']->setDefaults(['zamestnanecId'=>$zamestnanecId]);

    }


    function renderDetail($mzdovyComId)
    {
        $mzdovyCom = $this->mzdovyComRepository->getMzdovyCom($mzdovyComId);
        if($mzdovyCom){
          $mzdovyComBasicAttributes = $mzdovyCom->toArray();
          $mzdovyComBasicAttributes["zamestnanecName"] = $this->zamestnanecRepository->getName($mzdovyCom->zamestnanecId);
          $this->zamestnanecId = $mzdovyCom->zamestnanecId;
          //$mzdovyComBasicAttributes["zamestnanecName"] = $mzdovyCom->zamestnanec->billingFullName;

          $this['mzdovyComForm']->setDefaults($mzdovyComBasicAttributes);
        }else {
          $this->redirect("MzdovyCom:");
        }
    }


    public function handleCreateMzdovyCom()
    {

        if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
        }
    }

    protected function createComponentMzdovyComForm()
    {


        $form = $this->mzdovyComFormFactory->create($this->mzdovyComId);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Faktura byla aktualizována a změny uloženy.', 'alert-success');
            if($this->zamestnanecId)
              $this->redirect('Zamestnanec:');
            $this->redirect('MzdovyCom:');
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Faktura byla odstraněna.', 'alert-success');
          //  $this->redirect('MzdovyCom:');
          }

        };

        return $form;

    }
    protected function createComponentMzdovyComLogEntryForm()
    {
        $form = $this->mzdovyComLogEntryFormFactory->create($this->mzdovyComId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('MzdovyCom:');
          }
        };
        return $form;

    }

    public function createComponentMzdovyComControl()
    {
        return $this->mzdovyComControlFactory->create();
    }
    public function createComponentRozpadstrediskaControl()
    {
        return $this->rozpadstrediskaControlFactory->create();
    }
    public function createComponentMzdovyParControl()
    {
        return $this->mzdovyParControlFactory->create();
    }

    public function actionPrint($mzdovyComId)
    {
      $template = $this->templateFactory->createTemplate();

      $mzdovyCom = $this->mzdovyComRepository->getMzdovyCom($mzdovyComId);
      $template->mzdovyCom = $mzdovyCom;
      $template->zamestnanec = $this->zamestnanecRepository->getZamestnanec($mzdovyCom->zamestnanecId);
      $template->wRs = $this->wRRepository->getAllMzdovyCom($mzdovyComId);

      $formatedId = sprintf("%03d", $mzdovyCom->zamestnanecId);
      $weekCreated = date('W',$mzdovyCom->created->getTimestamp());

      $variabSymbol = "1712".$formatedId.$weekCreated;


      $template->variabSymbol = $variabSymbol;
      $template->dateToPay = date("d. m. Y",$mzdovyCom->created->getTimestamp()+604800+604800);



      $template->setFile(__DIR__ . '/templates/MzdovyCom/pdf.latte');



      $pdf = new mPDF();
      $pdf->WriteHTML($template);
      $pdf->Output("NES_faktura_".$variabSymbol.".pdf", \Mpdf\Output\Destination::DOWNLOAD);
      //$pdf->Output();

      $this->redirect('MzdovyCom:');
    }

}
