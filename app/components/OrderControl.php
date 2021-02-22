<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\WRRepository;
use App\Model\Repository\ZamestnanecRepository;
use Nette\Security\User;

use Tracy\Debugger;

Debugger::enable();



class WRControl extends Control
{

    private $wRRepository;
    private $zamestnanecRepository;

    /** @var User */
    private $user;

    /** @persistent */
    public $zamestnanecId;

    public function __construct(WRRepository $wRRepository, ZamestnanecRepository $zamestnanecRepository, Nette\Security\User $user)
    {
        parent::__construct();
        $this->wRRepository = $wRRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->user = $user;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->wRTypeFilterId;
        //$this->template->wRTypeFilterId = $this->type;
        //$this->template->wRTypes = $this->wRRepository->getAllWRTypes();
        $this->template->render(__DIR__ . '/order_control.latte');

    }


    public function createComponentWRGrid($name)
    {
        $this->zamestnanecId =$this->getPresenter()->zamestnanecId;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('pomerId');


        if(isset($this->getPresenter()->mzdovyComId))
          $grid->setDataSource($this->wRRepository->getAllMzdovyCom($this->getPresenter()->mzdovyComId));
        else {
          $grid->setDataSource($this->wRRepository->getAllZamestnanec($this->zamestnanecId));
        }

        $grid->addColumnLink('pomerId', 'id Pracovního poměru',  'WR:detail','pomerId',['pomerId'=>'pomerId'])->setSortable();
        $grid->addColumnDateTime('nastup', 'nástup')->setFormat('d.m.Y')->setSortable();
        $grid->addColumnDateTime('vystup', 'výstup')->setSortable();
        $grid->addColumnLink('zamestnanecId', 'zaměstnanec','Zamestnanec:detail','zamestnanecId',['zamestnanecId'=>'zamestnanecId'])->setReplacement($this->zamestnanecRepository->getZamestnanecNames());
        $grid->addColumnLink('prepoctenyUvazek', 'přepočtený úvazek', 'WR:detail','prepoctenyUvazek',['pomerId'=>'pomerId'])->setSortable();
        $grid->addColumnText('stredisko', 'středisko')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        //$grid->addColumnLink('fk_mzdovyCom', 'vyfakturováno', 'MzdovyCom:detail','fk_mzdovyCom',['mzdovyComId'=>'fk_mzdovyCom'])->addAttributes(["width" => "15%"])->setReplacement(['0'=>'ne'])->setSortable();
        // $grid->addColumnText('billingZip', 'psč')->addAttributes(["width" => "25%"]);
        // $grid->addColumnText('email', 'email')->addAttributes(["width" => "25%"]);
        // $grid->addColumnText('phone', 'telefon')->addAttributes(["width" => "25%"]);
      //  $grid->addAction('link2', 'Uložit pdf', 'WR:print');
      //  $grid->addAction('link', 'Detail', 'WR:detail');


        $grid->setDefaultPerPage(50);
        $grid->setDefaultSort(['pomerId' => 'DESC']);
        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }
    public function priceEdited($id,$value)
    {
      return $this->wRRepository->updatePrice($id,$value, $this->user->getId());
    }


}
