<?php

namespace FrontModule;

use App\Model\Repository\WRRepository;
use App\Model\Repository\OsobaRepository;
use App\Model\Repository\ZamestnanecRepository;
use Nette;



class HomepagePresenter extends BasePresenter
{
    /** @var ZamestnanecRepository */
    private $zamestnanecRepository;

    /** @var WRRepository */
    private $wRRepository;

    /** @var OsobaRepository */
    private $osobaRepository;

    public function __construct(ZamestnanecRepository $zamestnanecRepository,WRRepository $wRRepository,
                                OsobaRepository $osobaRepository)
    {
        parent::__construct();
        $this->zamestnanecRepository = $zamestnanecRepository;
        $this->wRRepository = $wRRepository;
        $this->osobaRepository =  $osobaRepository;

        //  $this->deviceTypeFilterId = '-1';
        //  $this->zamestnanecTypeFilterId = ALL;

    }

    public function execCurl($entity, $params = [])
    {
        $login = 'lach';
        $password = 'REDACTED';
        $endpoint  = 'API_HOST:81/charita/';
        //$params = array('foo' => 'bar');
        $url = $endpoint . $entity . '?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
        $result = curl_exec($ch);
        curl_close($ch);
        return($result);
    }
    public function actionImportEntity($entity)
    {
        $this->setView('default');
        $this->template->result = $this->execCurl($entity);
    }
    public function actionImportAddress()
    {
        $this->setView('default');
        $this->template->result = $this->execCurl('addresses');
    }
    public function actionImportPerson()
    {
        $this->setView('default');
        $persons = $this->execCurl('persons');

        foreach (json_decode($persons) as $person){
            $osoba = [];
            $osoba['osobaId'] =  $person->id;
            $osoba['osobniCislo'] =  $person->personalnumber;
            $osoba['narozeni'] =  $person->{'dateofbirth$date'};
            $osoba['objVerze'] =  $person->objversion;
            $osoba['jmeno'] =  $person->firstname.' '. $person->lastname;
            $this->osobaRepository->add($osoba);
        }
    }
    public function actionImportEmployee()
    {
        $this->setView('default');
         $employees = $this->execCurl('employees', ['select'=>'id, person_id, firstaccesstocompany, educationgrade_id, countedexperience, personalnumber, dateofbirth$date, employeename, sex']);

        foreach (json_decode($employees) as $employee){
            $zamestnanec = [];
            $zamestnanec['zamestnanecId'] =  $employee->id;
            $zamestnanec['osobaId'] =  $employee->person_id;
            $zamestnanec['nastup'] =  $employee->firstaccesstocompany;
            $zamestnanec['vzdelani'] =  $employee->educationgrade_id;
            $zamestnanec['zapoctenaPraxe'] =  $employee->countedexperience;
            $zamestnanec['osobniCislo'] =  $employee->personalnumber;
            $zamestnanec['narozeni'] =  $employee->{'dateofbirth$date'};
            $zamestnanec['jmeno'] =  $employee->employeename;
            $zamestnanec['pohlavi'] =  $employee->sex;
            $this->zamestnanecRepository->add($zamestnanec);
        }
    }
    public function actionImportWR()
    {
        $this->setView('default');
        $wrs = $this->execCurl('workingrelations');

        foreach (json_decode($wrs) as $wr){
            $mzdovyList = [];
            $mzdovyList['pomerId'] =  $wr->id;
            $mzdovyList['zamestnanecId'] =  $wr->employee_id;
            $mzdovyList['nastup'] =  $wr->{'dateofbegin$date'};
            $mzdovyList['vystup'] =  $wr->{'dateofend$date'};
            $mzdovyList['stredisko'] =  $wr->division_id;
            $mzdovyList['druhPP'] =  $wr->employpattern_id;
            $mzdovyList['pracovniZarazeni'] =  $wr->jobclasification_id;
            $mzdovyList['uvazekHodinTydne'] =  $wr->individualweekduty;
//            $mzdovyList['prepoctenyUvazek'] =  $wr->sex;
            $mzdovyList['pracKalendar'] = $wr->shiftcalendar_id;
            $mzdovyList['druhMzdy'] =  $wr->typeofwage; //0- hodidna, 1 mesic
            $mzdovyList['mzda'] =  $wr->wageamount;
//            $mzdovyList['priplatekZaRizeni'] =  $wr->sex;
//            $mzdovyList['osobniHodnoceni'] =  $wr->sex;
//            $mzdovyList['primaPece'] =  $wr->sex;
//            $mzdovyList['souladSS'] =  $wr->sex;

            $this->wRRepository->setTable('pracovnipomer');
            $this->wRRepository->add($mzdovyList);

        }
    }
    public function actionImportWLCommon()
    {
        $this->setView('default');
        $wrs = $this->execCurl('wagelistcommons', ['where' => "period_id eq '1A10000101'", 'select' => 'id, grosswagetotal, S_SuperGrossWageTotal, employee_id, wageperiod_id, S_HealthInsCompany, S_SocialInsCompany, period_id']);

        foreach (json_decode($wrs) as $wr){
            $mzdovyList = [];
            $mzdovyList['mzdovyComId'] =  $wr->id;
            $mzdovyList['zamestnanecId'] =  $wr->employee_id;
            $mzdovyList['periodId'] =  $wr->wageperiod_id;
            $mzdovyList['hrubaMzda'] =  $wr->grosswagetotal;
            $mzdovyList['mzdoveNaklady'] =  $wr->S_SuperGrossWageTotal;
            $mzdovyList['zdravotniPojistneZamestnavatel'] =  $wr->S_HealthInsCompany;
            $mzdovyList['socialniPojistneZamestnavatel'] =  $wr->S_SocialInsCompany;

            $this->wRRepository->setTable('mzdovylistcom');
            $this->wRRepository->add($mzdovyList);
        }
    }
    public function actionImportWLPartial()
    {
        $this->setView('default');
        $wrs =$this->execCurl('wagelistpartials', ['where' => "period_id eq '1A10000101'", 'select'=> 'workingrelation_id, S_OverTimeTotal, id, employee_id, oldrewardperiodlength, oldtwelvemonthsreward, paidholidaytotal, wageperiod_id, grosswagetotal,totalworktime, oldrewardlongtermtotal, S_PaidFreeTotal, HolidayDrawHours, S_ArmyExerciseTotal, S_SickAbsences']);
        foreach (json_decode($wrs) as $wr){
            $mzdovyList = [];
            $mzdovyList['mzdovyParId'] =  $wr->id;
            $mzdovyList['zamestnanecId'] =  $wr->employee_id;
            $mzdovyList['pracovnipomerId'] =  $wr->workingrelation_id;
            $mzdovyList['periodId'] =  $wr->wageperiod_id;
            $mzdovyList['hrubaMzda'] =  $wr->grosswagetotal;

            $mzdovyList['premie'] =  $wr->oldrewardlongtermtotal;
            $mzdovyList['mesicniOdmeny'] =  $wr->oldrewardperiodlength;
            $mzdovyList['trinactyPlat'] =  $wr->oldtwelvemonthsreward;
            $mzdovyList['odmenaZaDelsiOdbobi'] =  $wr->oldrewardlongtermtotal;

            $mzdovyList['dovolena'] =  $wr->paidholidaytotal;
            $mzdovyList['odpracovaneHodinyVcetnePrescasu'] =  $wr->totalworktime;
            $mzdovyList['zTohoPrescas'] =  $wr->S_OverTimeTotal;
            $mzdovyList['placeneVolno'] =  $wr->S_PaidFreeTotal;
            $mzdovyList['dovolena'] =  $wr->HolidayDrawHours;
            $mzdovyList['nemoc'] =  $wr->S_SickAbsences;

            $this->wRRepository->setTable('mzdovylistpar');
            $this->wRRepository->add($mzdovyList);
        }
    }

    public function actionImportWagePeriod()
    {

    }
    public function actionImportPeriod()
    {

    }

}



