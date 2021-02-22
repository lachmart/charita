<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\InvoiceRepository;
use App\Model\Repository\zamestnanecRepository;

use Tracy\Debugger;

Debugger::enable();



class InvoiceControl extends Control
{

    private $invoiceRepository;
    private $zamestnanecRepository;

    /** @persistent */
    public $zamestnanecId;
    /** @persistent */
    public $pomerId;

    public function __construct(InvoiceRepository $invoiceRepository, ZamestnanecRepository $zamestnanecRepository)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->zamestnanecRepository = $zamestnanecRepository;
    }

    public function render()
    {
      //  $this->type = (int)$this->getPresenter()->invoiceTypeFilterId;
        //$this->template->invoiceTypeFilterId = $this->type;
        //$this->template->invoiceTypes = $this->invoiceRepository->getAllInvoiceTypes();
        $this->template->render(__DIR__ . '/invoice_control.latte');

    }


    public function createComponentInvoiceGrid($name)
    {
        $this->zamestnanecId =$this->getPresenter()->zamestnanecId;
        $this->pomerId = $this->getPresenter()->pomerId;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('mzdovyComId');

        if($this->pomerId)
          $grid->setDataSource($this->invoiceRepository->getAllPomer($this->pomerId));
        else
          $grid->setDataSource($this->invoiceRepository->getAllZamestnanec($this->zamestnanecId));

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
         $grid->addColumnLink('mzdovyComId', 'číslo mzdoveho listu', 'Invoice:detail','mzdovyComId',['mzdovyComId'=>'mzdovyComId'])->setSortable()->addAttributes(["width" => "10%"]);
        // $grid->addColumnLink('created', 'vytvořeno', 'Invoice:detail','created',['mzdovyComId'=>'mzdovyComId'])->setSortable()->addAttributes(["width" => "25%"]);
        $grid->addColumnDateTime('periodId', 'měsíc')->setSortable();

        //$grid->addColumnText('created', 'vytvořeno')->addAttributes(["width" => "25%"])->setFilterText();
        $grid->addColumnLink('zamestnanecId', 'Zaměstnanec','Zamestnanec:detail','zamestnanecId',['pomerId'=>'zamestnanecId'])->addAttributes(["width" => "25%"]);
        $grid->addColumnLink('hrubaMzda', 'hrubá mzda','Invoice:detail','hrubaMzda',['mzdovyComId'=>'mzdovyComId'])->setSortable();

        //$grid->addColumnText('email', 'email')->addAttributes(["width" => "25%"])->setFilterText();
        //  $grid->addColumnText('phone', 'telefon')->addAttributes(["width" => "25%"])->setFilterText();
      //  $grid->addAction('link', 'Přidat nákup', 'Invoice:create')->addAttributes(["width" => "25%"]);
        // $grid->addAction('link2', 'Uložit pdf', 'Invoice:print')->addAttributes(["width" => "25%"]);
        // $grid->addAction('link', 'Detail', 'Invoice:detail');

        $grid->setDefaultPerPage(50);
        $grid->setDefaultSort(['mzdovyComId' => 'DESC']);
        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }



}
