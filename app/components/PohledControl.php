<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\PohledRepository;
use App\Model\Repository\ZamestnanecRepository;
use Nette\Security\User;

use Tracy\Debugger;

Debugger::enable();



class PohledControl extends Control
{

    private $pohledRepository;
    private $zamestnanecRepository;

    /** @var User */
    private $user;

    /** @persistent */
    public $zamestnanecId;

    public function __construct(PohledRepository $pohledRepository, ZamestnanecRepository $zamestnanecRepository, Nette\Security\User $user)
    {
        parent::__construct();
        $this->pohledRepository = $pohledRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->user = $user;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->pohledTypeFilterId;
        //$this->template->pohledTypeFilterId = $this->type;
        //$this->template->pohledTypes = $this->pohledRepository->getAllPohledTypes();
        $this->template->render(__DIR__ . '/pohled_control.latte');

    }


    public function createComponentPohledGrid($name)
    {
        $this->zamestnanecId =$this->getPresenter()->zamestnanecId;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('mzdovyComId');

        $grid->setDataSource($this->pohledRepository->getAll());

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
         $grid->addColumnLink('mzdovyComId', 'číslo mzdoveho listu', 'Invoice:detail','mzdovyComId',['mzdovyComId'=>'mzdovyComId'])->setSortable()->addAttributes(["width" => "10%"]);
        // $grid->addColumnLink('created', 'vytvořeno', 'Invoice:detail','created',['mzdovyComId'=>'mzdovyComId'])->setSortable()->addAttributes(["width" => "25%"]);
        $grid->addColumnDateTime('mesic', 'měsíc')->setFormat('m/Y')->setSortable();

        $grid->addColumnLink('zamestnanecId', 'zaměstnanec','Zamestnanec:detail','zamestnanecId',['zamestnanecId'=>'zamestnanecId'])->setReplacement($this->zamestnanecRepository->getZamestnanecNames());
        $grid->addColumnLink('FK_pracovniPomer', 'p. poměr','WR:detail','FK_pracovniPomer',['pomerId'=>'FK_pracovniPomer']);
        $grid->addColumnDateTime('nastup', 'nástup')->setFormat('d.m.Y')->setSortable();
        $grid->addColumnDateTime('vystup', 'výstup')->setSortable();
        $grid->addColumnText('primaPece', 'přímá péče')->setSortable();
        $grid->addColumnLink('hrubaMzda', 'hrubá mzda','Invoice:detail','hrubaMzda',['mzdovyComId'=>'mzdovyComId'])->setSortable();
        $grid->addColumnText('stredisko', 'středisko')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnLink('prepoctenyUvazek', 'přepočtený úvazek', 'WR:detail','prepoctenyUvazek',['pomerId'=>'pomerId'])->setSortable();
        $grid->addColumnText('stredisko2', '100')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko3', '571')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko4', '631')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko5', '210')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko6', '210')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko7', '210')->setEditableCallback([$this, 'priceEdited'])->setSortable();

        //  $grid->addColumnText('phone', 'telefon')->addAttributes(["width" => "25%"])->setFilterText();
        //  $grid->addAction('link', 'Přidat nákup', 'Invoice:create')->addAttributes(["width" => "25%"]);
         $grid->addAction('link2', 'Rozdělit podle klíče', 'Invoice:print');
         $grid->addAction('link', 'uzamčít', 'Invoice:detail');

        $grid->setDefaultPerPage(50);
        $grid->setDefaultSort(['mzdovyComId' => 'DESC']);
        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }
    public function priceEdited($id,$value)
    {
      return $this->pohledRepository->updatePrice($id,$value, $this->user->getId());
    }


}
