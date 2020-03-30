<?php
namespace App\Components;

use Nette;
use Ublaboo\DataGrid\DataGrid;
use Nette\Application\UI\Form;
use App\Model\Repository\InvoiceRepository;
use App\Model\Repository\customerRepository;

use Tracy\Debugger;

Debugger::enable();



class InvoiceControl extends Control
{

    private $invoiceRepository;
    private $customerRepository;

    /** @persistent */
    public $customerId;

    public function __construct(InvoiceRepository $invoiceRepository, CustomerRepository $customerRepository)
    {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->customerRepository = $customerRepository;
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
      $this->customerId =$this->getPresenter()->customerId;
        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('invoiceId');

        $grid->setDataSource($this->invoiceRepository->getAllCustomer($this->customerId));

        //$grid->addColumnText('billingFullName', 'jméno')->setSortable()->addAttributes(["width" => "15%"]);
         $grid->addColumnLink('invoiceId', 'číslo faktury', 'Invoice:detail','invoiceId',['invoiceId'=>'invoiceId'])->setSortable()->addAttributes(["width" => "10%"]);
        // $grid->addColumnLink('created', 'vytvořeno', 'Invoice:detail','created',['invoiceId'=>'invoiceId'])->setSortable()->addAttributes(["width" => "25%"]);
        $grid->addColumnDateTime('created', 'vytvořeno')->setFormat('d.m.Y H:i')->setSortable();

        //$grid->addColumnText('created', 'vytvořeno')->addAttributes(["width" => "25%"])->setFilterText();
        $grid->addColumnLink('fk_customer', 'zákazník','Customer:detail','fk_customer',['customerId'=>'fk_customer'])->setReplacement($this->customerRepository->getCustomerNames())->addAttributes(["width" => "25%"]);
        $grid->addColumnLink('total', 'celkem','Invoice:detail','total',['invoiceId'=>'invoiceId'])->setSortable();

        //$grid->addColumnText('email', 'email')->addAttributes(["width" => "25%"])->setFilterText();
        //  $grid->addColumnText('phone', 'telefon')->addAttributes(["width" => "25%"])->setFilterText();
      //  $grid->addAction('link', 'Přidat nákup', 'Invoice:create')->addAttributes(["width" => "25%"]);
        $grid->addAction('link2', 'Uložit pdf', 'Invoice:print')->addAttributes(["width" => "25%"]);
        $grid->addAction('link', 'Detail', 'Invoice:detail');

        $grid->setDefaultPerPage(50);
        $grid->setDefaultSort(['invoiceId' => 'DESC']);
        $grid->setTranslator($this->translator);
        //$grid->addFilterMultiSelect('buildingName', 'buildingName', ['První budova', 'Druhá']);
        $grid->setStrictSessionFilterValues(FALSE);
        return $grid;
    }



}
