<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\ZamestnanecRepository;
use App\Model\Repository\WRRepository;

use Tracy\Debugger;

Debugger::enable();



class ZamestnanecControl extends Control
{

    private $zamestnanecRepository;
    private $wRRepository;

    public function __construct(ZamestnanecRepository $zamestnanecRepository, WRRepository $wRRepository)
    {
        parent::__construct();
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->wRRepository = $wRRepository;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->zamestnanecTypeFilterId;
        //$this->template->zamestnanecTypeFilterId = $this->type;
        //$this->template->zamestnanecTypes = $this->zamestnanecRepository->getAllZamestnanecTypes();
        $this->template->render(__DIR__ . '/zamestnanec_control.latte');

    }


    public function createComponentZamestnanecGrid($name)
    {
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('zamestnanecId');

        $grid->setDataSource($this->zamestnanecRepository->getAll());

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
        $grid->addColumnLink('zamestnanecId', 'číslo zaměstnaneca', 'detail','zamestnanecId',['zamestnanecId'=>'zamestnanecId'])->setSortable()->addAttributes(["width" => "10%"])->setFilterText();
        $grid->addColumnLink('jmeno', 'Zaměstnanec', 'Zamestnanec:detail','jmeno',['zamestnanecId'=>'zamestnanecId'])->setSortable()->addAttributes(["width" => "20%"])->setFilterText();
        $grid->addColumnLink('pohlavi', 'pohlaví','Zamestnanec:detail','pohlavi',['zamestnanecId'=>'zamestnanecId'])->setFilterText();
        $grid->addColumnDateTime('narozeni', 'narození','narozeni',['zamestnanecId'=>'zamestnanecId'])->setFormat('Y')->addAttributes(["width" => "20%"])->setFilterText();
        $grid->addColumnDateTime('nastup', 'nástup','nastup',['zamestnanecId'=>'zamestnanecId'])->setFormat('d.m.Y')->setFilterText();

        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setDefaultSort(['zamestnanecId' => 'DESC']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }



}
