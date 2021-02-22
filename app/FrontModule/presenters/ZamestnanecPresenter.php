<?php

/**
 *
 *
 */

namespace FrontModule;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls;
use Ublaboo\DataGrid\DataGrid;
use App\Model\Repository\ZamestnanecRepository;
use App\Model\Repository\WRRepository;
use App\Forms\IZamestnanecFormFactory;
use App\Forms\ZamestnanecForm;
use App\Components\IZamestnanecControlFactory;
use App\Components\IWRControlFactory;
use App\Components\IMzdovyComControlFactory;

//define("ALL", -1);

class ZamestnanecPresenter extends BasePresenter
{
    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;

    /** @var WRRepository */
    private $wRRepository;

    /**
     * @var IZamestnanecControlFactory
     * @inject
     */
    public $zamestnanecControlFactory;

    /**
     * @var IMzdovyComControlFactory
     * @inject
     */
    public $mzdovyComControlFactory;
    /**
     * @var IWRControlFactory
     * @inject
     */
    public $wRControlFactory;

    /** @var \IZamestnanecFormFactory */
    private $zamestnanecFormFactory;

    /** @persistent */
    public $pomerId;

    /** @persistent */
    public $zamestnanecId;

    public function __construct(ZamestnanecRepository $zamestnanecRepository,WRRepository $wRRepository,
                                ZamestnanecForm $zamestnanecFormFactory)
    {
        parent::__construct();
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->wRRepository = $wRRepository;
        $this->zamestnanecFormFactory =  $zamestnanecFormFactory;

      //  $this->deviceTypeFilterId = '-1';
      //  $this->zamestnanecTypeFilterId = ALL;

    }


    function renderDefault()
    {
        //$this->template->zamestnanecTypes = $this->zamestnanecRepository->getAllZamestnanecTypes();
        //\var_dump($this->user->getIdentity());
    }


    function renderCreate($zamestnanecTypeId)
    {
      $this->template->showMzdovyComCreate = 0;
      $this->zamestnanecId = 0;
       // $this->template->zamestnanecAttributes = $this->zamestnanecRepository->getAllZamestnanecAttributesForZamestnanecType($zamestnanecTypeId);
        //$this->zamestnanecTypeId = $zamestnanecTypeId;
    }


    function renderDetail($zamestnanecId)
    {
        $this->template->showMzdovyComCreate = 0;
        $zamestnanec = $this->zamestnanecRepository->get($zamestnanecId);
        if($zamestnanec){
          $this->zamestnanecId = $zamestnanecId;
          $this->template->zamestnanec = $zamestnanec;
          //$this->template->showMzdovyComCreate = count($this->wRRepository->getWRsWithoutMzdovyCom($zamestnanecId));
          $zamestnanecBasicAttributes = $zamestnanec->toArray();
          $this['zamestnanecForm']->setDefaults($zamestnanecBasicAttributes);

        }else {
          $this->redirect("Zamestnanec:");
        }
    }


    public function handleCreateZamestnanec()
    {

        if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
        }
    }

    public function handleCreateLogEntry()
    {
      //  if ($this->isAjax()) {
            $this->payload->isModal = TRUE;
            $this->redrawControl("modal");
      //  }
    }

    protected function createComponentZamestnanecForm()
    {

        //TODO zamestnanec
        //$zamestnanecTypeId = $this->zamestnanecTypeId;


       // if(!$zamestnanecTypeId) $zamestnanecTypeId = 0;

        $form = $this->zamestnanecFormFactory->create($this->zamestnanecId);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            if($this->zamestnanecId){
              $this->flashMessage('Zaměstnanec byl aktualizován a změny uloženy.', 'alert-success');
              $this->redirect('Zamestnanec:detail');
            }
            else{
              $this->flashMessage('Zaměstnanec byl vytvořen.', 'alert-success');
              $this->redirect('Zamestnanec:');
            }
          }
          if($form['delete']->isSubmittedBy()){
            $this->flashMessage('Zaměstnanec byl odstraněn.', 'alert-success');
            $this->redirect('Zamestnanec:');
          }

        };



        return $form;

    }
    protected function createComponentZamestnanecLogEntryForm()
    {
        $form = $this->zamestnanecLogEntryFormFactory->create($this->zamestnanecId, $this->user->getidentity()->userId, $this->user->getidentity()->userName);

        $form->onSuccess[] = function (Nette\Application\UI\Form $form) {
          if($form['submit']->isSubmittedBy()){
            $this->flashMessage('Záznam byl přidán.', 'alert-success');
          //  $this->redirect('Zamestnanec:');
          }
        };
        return $form;

    }


    public function createComponentZamestnanecControl()
    {
        return $this->zamestnanecControlFactory->create();
    }


    public function createComponentWRControl()
    {
        return $this->wRControlFactory->create();
    }
    public function createComponentMzdovyComControl()
    {
        return $this->mzdovyComControlFactory->create();
    }



}
