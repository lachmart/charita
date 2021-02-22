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



class RozpadstrediskaControl extends Control
{

    private $wRRepository;
    private $zamestnanecRepository;

    /** @var User */
    private $user;

    /** @persistent */
    public $mzdovyComId;

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
        $this->template->render(__DIR__ . '/rozpadstrediska_control.latte');

    }


    public function createComponentRozpadstrediskaGrid($name)
    {
        $this->mzdovyComId =$this->getPresenter()->mzdovyComId;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('rozpadNaStrediskoId');


        if(isset($this->getPresenter()->mzdovyComId))
          $grid->setDataSource($this->wRRepository->getAllRozpad($this->getPresenter()->mzdovyComId));
        else {
          $grid->setDataSource($this->wRRepository->getAllZamestnanec($this->zamestnanecId));
        }

        //$grid->addColumnLink('rozpadNaStrediskoId', 'id',  'WR:detail','rozpadNaStrediskoId',['rozpadNaStrediskoId'=>'rozpadNaStrediskoId'])->setSortable();
        $grid->addColumnText('verze', 'verze')->setSortable();
        $grid->addColumnText('stredisko2', '100')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko3', '571')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko4', '631')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko5', '210')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko6', '210')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        $grid->addColumnText('stredisko7', '210')->setEditableCallback([$this, 'priceEdited'])->setSortable();

        //$grid->addColumnLink('FK_', 'zaměstnanec','Zamestnanec:detail','zamestnanecId',['zamestnanecId'=>'zamestnanecId'])->setReplacement($this->zamestnanecRepository->getZamestnanecNames());
        //$grid->addColumnLink('list', 'nákupní seznam', 'WR:detail','list',['pomerId'=>'pomerId'])->setSortable();
        //$grid->addColumnText('stredisko', 'středisko')->setEditableCallback([$this, 'priceEdited'])->setSortable();
        //$grid->addColumnLink('fk_invoice', 'vyfakturováno', 'Invoice:detail','fk_invoice',['mzdovyComId'=>'fk_invoice'])->addAttributes(["width" => "15%"])->setReplacement(['0'=>'ne'])->setSortable();
        // $grid->addColumnText('billingZip', 'psč')->addAttributes(["width" => "25%"]);
        // $grid->addColumnText('email', 'email')->addAttributes(["width" => "25%"]);
        // $grid->addColumnText('phone', 'telefon')->addAttributes(["width" => "25%"]);
      //  $grid->addAction('link2', 'Uložit pdf', 'WR:print');
      //  $grid->addAction('link', 'Detail', 'WR:detail');


        $grid->setDefaultPerPage(50);
        //$grid->setDefaultSort(['pomerId' => 'DESC']);
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
