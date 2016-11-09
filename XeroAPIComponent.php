<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;

use Cake\Utility\Hash;
use Cake\Log\Log;
use App\Controller\Component\XeroAPI\XeroOAuth;
use Cake\Core\App;

define ( 'BASE_PATH', dirname(__FILE__).DS.'XeroAPI' );

/**
 * XeroAPI component
 */

class XeroAPIComponent extends Component
{
    public $transport;
    public $XeroXML ;
    private $_invoiceEndpoint = 'Invoices';
    public $components = ['XeroAPI\XeroInvoice','App\Controllor\Component\XeroAPI\XeroXML'];

    protected $error = array();

    protected $set = '';

    protected $item = '';
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'application_type' => 'Private', // possible values: 'Private' !!! Unsupported: 'Public' or 'Partner'
        'oauth_callback' => 'oob',
        'user_agent' => 'XeroOAuth-PHP Private App Test',
        'core_version' => '2.0',
        'payroll_version' => '1.0',
        'file_version' => '1.0',
        //auth credentials
        'consumer_key' => '',
        'shared_secret' => '',
        //Optional for Private or Partner apps
        'rsa_private_key' => '', //typical path: 'APP . 'Config' .DS. 'certs' .DS.'privatekey.pem'
        'rsa_public_key' => '', //typical path: 'APP . 'Config' .DS. 'certs' .DS.'publickey.cer'
    ];



    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $registry A ComponentRegistry for this component
     * @param array $config Array of config.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        //$this->_session = $registry->getController()->request->session();
    }
    /**
     * Initialize component
     *
     * @param Controller $controller Instantiating controller
     * @return void
     */
    public function initialize(array $config) {
        $signatures = $this->_defaultConfig;
        if (!in_array($signatures['application_type'], array("Private", "Partner"))) {
            unset($signatures ['rsa_private_key']);
            unset($signatures ['rsa_public_key']);
        }else{

            $signatures['rsa_private_key'] = BASE_PATH . '\certs\privatekey.pem';
            $signatures['rsa_public_key'] = BASE_PATH . '\certs\publickey.cer';
        }
        $this->transport = new XeroOAuth ($signatures);
        if (!isset($signatures['curl_cainfo'])){
            $this->transport->config['curl_cainfo'] = BASE_PATH.DS.'certs'.DS.'ca-bundle.crt';
        }

        $initialCheck = $this->transport->diagnostics ();

        if (count($initialCheck) > 0) {
            // you could handle any config errors here, or keep on truckin if you like to live dangerously
            foreach ( $initialCheck as $check ) {
                Log::write('error', $check);
            }
        } else {
            $this->transport->config['consumer_key'] = Configure::read('Xero.auth.consumer_key');
            $this->transport->config['shared_secret'] = Configure::read('Xero.auth.shared_secret');
            $this->transport->config['session_handle'] = '';

            //debug($this->transport);
        }

        //App::import('Controller/Component/XeroAPI','XeroXML');
        $className = App::className('XeroXML', 'Controller/Component/XeroAPI');
        $this->XeroXML = new $className();
    }





//=================== Xero Invoice functions ============================
    public function getInvoices($params=array())
    {
        $endpoint = $this->_invoiceEndpoint;
        return $this->read($endpoint,$params);
    }

    public function getInvoiceById($id='',$params=array()){
        $endpoint = $this->_invoiceEndpoint.((empty($id)? '': '/')).$id;
        return $this->read($endpoint,$params);
    }


    public function deleteInvoiceByInvoiceNumber($id){
        if(empty($id)) {
            return false;
        }
        $xml = $this->XeroXML->buildDeleteInvoiceXML($id);
       // debug($xml);
        return $this->postData($this->_invoiceEndpoint, array(), $xml);

    }

    private function getInvoiceXMLData($data)
    {
        return $this->XeroXML->buildInvoiceXML($data);
    }

//    public function setAsCustomerInvoice()
//    {
//         $this->XeroXML->setAsCustomerInvoice();
//    }
//
//    public function setAsSupplierInvoice()
//    {
//         $this->XeroXML->setAsSupplierInvoice();
//    }

    public function postInvoiceToXero($data){
        $xmlData = $this->getInvoiceXMLData($data);
        //debug($xmlData);
        return $this->request('POST', array('Invoices', 'core'), array(), $xmlData);
    }

    public function postCustomerInvoiceToXero($data)
    {
        $this->XeroXML->setAsCustomerInvoice();
        return $this->postInvoiceToXero($data);
    }

    public function postSupplierInvoiceToXero($data)
    {
        $this->XeroXML->setAsSupplierInvoice();
        return $this->postInvoiceToXero($data);
    }

    public function getInvoicesFromTimeSheetData($timeSheetData){
        $invoiceIds = array_filter(Hash::extract($timeSheetData, '{n}.TimeSheet.invoice_id'));
        //  $invoiceIds[0]='INV-114700';

        //debug($timeSheetData);
        // if($primary){
        $timeSheetData = Hash::insert($timeSheetData, '{n}.Invoice', array());
        // debug($invoiceIds);
        if(!empty($invoiceIds)){
            foreach ($invoiceIds as $key=>$invoiceId){
                $invoiceIds[$key] = 'InvoiceID = Guid("'.$invoiceId.'")';
            }

            $response = $this->getInvoices(array('where' => implode(' OR ', $invoiceIds)));
            //debug($response);
            if(is_array($response)){
                foreach($timeSheetData as $key=>$result){
                    if(!empty($result['TimeSheet']['invoice_id'])){
                        $invoice = Hash::extract($response["Invoices"], "{n}[InvoiceID=/".$result['TimeSheet']['invoice_id']."/]");

                        if(isset($invoice[0])){
                            $timeSheetData[$key]['Invoice'] = $invoice[0];
                        }
                    }
                }
            }
        }
        return $timeSheetData;
        // }
    }

    public function getInvoicesFromTimeSheetDataByIDAndInvoice($timeSheets)
    {
        $timeSheetNumberCondition=array();
        $timeSheetNumber=array();
        foreach($timeSheets as $result)
        {
            array_push($timeSheetNumberCondition,'InvoiceNumber=="'.$this->getTimeSheetInvoiceNumber($result).'"');
            array_push($timeSheetNumber,$this->getTimeSheetInvoiceNumber($result));
        }
        $response = $this->getInvoices(array('where' => implode(' OR ', $timeSheetNumberCondition)));
        //debug($response["Invoices"]);
        if(is_array($response)){
            $i = 0;
            foreach($timeSheets as $result){
                if(!empty($result['invoice_id'])){
                    $invoice = Hash::extract($response["Invoices"], "{n}[InvoiceNumber=/".$timeSheetNumber[$i]."/]");
                    //debug($invoice);
                    if(isset($invoice[0])){
                        $timeSheets[$i]->Invoice = end($invoice);
                    }
                    $i++;
                }
                //debug($timeSheets[$key]);
            }
        }
        return $timeSheets;
    }

    public function getInvoicesFromLessonsByInvoiceNumber($lessons)
    {
        $lessonNumberCondition=array();
        $lessonNumbers=array();
        $lessonIDs = array();
        foreach($lessons as $lesson)
        {
            if(strcmp($lesson['Student']['has_credit'],'Postpaid') != 0) {
                $invoiceNumber = $this->XeroXML->genPrepaidInvoiceNumber($lesson);
                array_push($lessonIDs,$lesson['id']);
                if(!in_array($invoiceNumber,$lessonNumbers)){

                    array_push($lessonNumbers,$invoiceNumber);
                    array_push($lessonNumberCondition, 'InvoiceNumber=="' . $invoiceNumber . '"');
                }

            }else {
                $invoiceNumber = $this->XeroXML->genStudentXeroInvoiceNumber($lesson['id']);
                array_push($lessonNumberCondition, 'InvoiceNumber=="' . $invoiceNumber . '"');

                array_push($lessonNumbers,$invoiceNumber);
            }
        }
        //debug($lessonNumbers);
        $response = $this->getInvoices(array('where' => implode(' OR ', $lessonNumberCondition)));
        //debug($response);
        if(strcmp($lesson['Student']['has_credit'],'Postpaid') != 0) {
            $invoices = $response['Invoices'];
            $xeroInvoice = array();
            $tmpInvoiceNumbers = "";
            foreach ($invoices as $invoice):
                if(strcmp($invoice['InvoiceNumber'],$tmpInvoiceNumbers) != 0)
                {
                    $tmpInvoiceNumbers = $invoice['InvoiceNumber'];
                    //$res = $this->getInvoiceById($invoice['InvoiceID']);
                }else{
                    $xeroInvoice[$tmpInvoiceNumbers] = $invoice['InvoiceID'];
                }
                endforeach;
                foreach ($xeroInvoice as $invoiceID){
                    $inv = $this->getInvoiceById($invoiceID);
                    $lineItems = $inv['Invoices'][0]['LineItems'];
                    //debug($lessons);
                    foreach ($lineItems as $item){
                        $exp = explode(' ',$item['Description']);
                        $lessonKeyID = preg_replace('/\[(\d+)\]/','$1',$exp[0]);
                        $index = array_search($lessonKeyID,$lessonIDs);
                        $lessons[$index]->Invoice = $inv["Invoices"][0];
                    }

                }
            //debug($lessons[$index]->Invoice);
        }else{
            if(is_array($response)){
                $i = 0;
                foreach($lessons as $result){
                    $invoice = Hash::extract($response["Invoices"], "{n}[InvoiceNumber=/".$lessonNumbers[$i]."/]");
                    //debug($invoice);
                    if(isset($invoice[0])){
                        $lessons[$i]->Invoice = end($invoice);
                    }
                    $i++;
                    //debug($timeSheets[$key]);
                }
            }
        }
        return $lessons;
    }

    //================= End  =========================

    //================= Xero Tutor contact functions ======================
    public function getTutorById($id='',$tutorData)
    {
        $response = $this->getXeroTutorById($id);
        if(!$response) {
            if (isset($response[0])) {
                $tutorData[0]['Contact'] = $response[0];
            }
        }
        return $tutorData;
    }

    public function addTutor($data)
    {
        $xeroData = $this->XeroXML->buildTutorInfoXML($data,true);
        //debug($xeroData);
        return $this->addContact($xeroData);
    }

    public function updateTutor($data, $contactId)
    {
        $xeroData = $this->XeroXML->buildTutorInfoXML($data,true,$contactId);
        //debug($xeroData);
        return $this->addContact($xeroData);
    }

//    public function getTutor($id)
//    {
//        return $this->getContact('TUTOR-'.$id);
//    }

    public function getXeroTutorById($id='',$params=array(), $xml="")
    {
        return $this->getContacts('TUTOR-'.$id,$params,$xml);
    }

    //================= End  ======================


    //================= Xero Student contact functions ======================
    public function getStudent($id)
    {
        return $this->getContact('STUDENT-'.$id);
    }

    public function addStudent($data)
    {
        $xeroData = $this->XeroXML->buildStudentInfoXML($data);

        return $this->addContact($xeroData);
    }

    public function updateStudent($data, $contactId)
    {
        $xeroData = $this->XeroXML->buildStudentInfoXML($data,$contactId);
        //debug($xeroData);
        return $this->addContact($xeroData);
    }

    public function deleteStudent($id)
    {
        $student = $this->getStudent($id);
        //debug($student);
        $xeroData = $this->XeroXML->buildDeleteContact($student['Contacts'][0]['ContactID']);

        return $this->addContact($xeroData);
    }

    //================= End  ======================

    //================= Xero Guardian contact functions ======================
    public function addGuardian($data)
    {
        $xeroData = $this->XeroXML->buildGuardianInfoXML($data);
        return $this->addContact($xeroData);
    }

    //================= End  ======================

    //================= Xero Contacts functions ======================
    public function addContact($xmlData)
    {
        return $this->postData('Contacts',array(),$xmlData);
    }

    public function getContact($id,$params=array())
    {
        $result = $this->getContacts($id,$params);
        return $result;
    }

    public function getContacts($id='',$params=array())
    {
        $endpoint = 'Contacts'.((empty($id)? '': '/')).$id;
        $result = $this->read($endpoint,$params);

        return $result;
    }

    //====================  End ========================

    //================= AP Tutoring functions ======================
    public function getTimeSheetInvoiceNumber($timeSheetData)
    {
        return $this->XeroXML->genTimeSheetXeroInvoiceNumber($timeSheetData);
    }


    public function getInvoiceNumber($id)
    {
        return $this->XeroXML->genStudentXeroInvoiceNumber($id);
    }

    public function getInvoiceNumberFromLessonId($id)
    {
        return $this->XeroXML->genStudentXeroInvoiceNumber($id);
    }
    public function genInvoiceID($id)
    {
        return $this->XeroXML->genInvoiceID($id);
    }



    //====================  End ========================

    //================= Xero Credit Note functions ======================

    public function getCreditNoteNumberByLessonId($id)
    {
        return 'CN-'.$this->getInvoiceNumber($id);
    }
    public function getCreditNoteNumberByInvoiceNumber($id)
    {
        return 'CN-'.$id;
    }

    public function createCreditNote($invoiceData)
    {
        $contactId = '';
        $invoice = $invoiceData['Invoices'][0];
        $contactId = $invoice['Contact']['ContactID'];
        $creditNoteNumber = $this->getCreditNoteNumberByInvoiceNumber($invoice['InvoiceNumber']);
        //debug($invoice);
        $xml = $this->XeroXML->buildCreditNoteXML($contactId,$creditNoteNumber,$invoice);
       // debug($xml);
        return $this->addCreditNote($xml);

    }

    public function addCreditNote($xmlDate)
    {
        $result = $this->postData('CreditNotes',array(),$xmlDate);
        return $result;
    }

    public function getCreditNote($id)
    {
        $result = $this->read('CreditNotes/'.$id);
        return $result;
    }

    //=================== End  =========================



    //================= Xero API main functions ======================
    public function read($endpoint, $params = array(), $xml = ""){

        $result = $this->request('GET', array($endpoint, 'core'),$params,$xml);
        //debug($result);
        return $result;
    }

    public function postData($endpoint, $params = array(), $xml = "")
    {
        $result = $this->request('POST', array($endpoint, 'core'),$params,$xml);
        //debug($result);
        return $result;
    }

    public function request($method, $url =array(), $params = array(), $xml = ""){
        $this->error = array();
        if(is_array($url)){
            $url = $this->transport->url($url[0], $url[1]);
        }
        $response = $this->transport->request($method, $url, $params, $xml, 'json');
        //debug($response);
        $this->transport->response['format'] = 'assoc';
        $responseData = $this->transport->parseResponse(
            $this->transport->response['response'],
            $this->transport->response['format']
        );
//        if(is_array($responseData)){
//            foreach ($responseData as $key=>$object){
//                $responseData[$key] = Set::reverse($object);
//            }
//        } else {
//            $responseData = Set::reverse($responseData);
//        }
       //
 //        debug($response);
        //debug($responseData);
        if(is_array($responseData)){
            foreach ($responseData as $key=>$object){
                $responseData[$key] = $object;
            }
        }
        //debug($responseData);
        //debug($this->transport->config);
        //debug( $response);
        if($response['code'] == 200){
            //debug( $response);
            return $responseData;
        } else {
            $this->error = array(	'ErrorNumber' => '0', 'Message' => 'Unknown error' );
            if(empty($responseData)){
                $this->error['ErrorNumber'] = $response['code'];
                $this->error['Message'] = $response['response'];
            } else {
                $this->error['ErrorNumber'] = $responseData['ErrorNumber'];
                $this->error['Message'] = $responseData['Message'];
            }
            if(isset($responseData['Elements'])&&is_array($responseData['Elements'])) {
                foreach ($responseData['Elements'] as $element){
                    if(isset($element['ValidationErrors'])&&!empty($element['ValidationErrors'])){
                        foreach($element['ValidationErrors'] as $subError){
                            $this->error['Message'] .= ' '.$subError['Message'];
                        }
                    }
                }
                $this->error['ErrorData'] = $responseData['Elements'];
            }
            //debug( $responseData);
            return false;
        }

    }

    public function getLastError(){
        $error = $this->error;
        $this->error = array();
        return $error;
    }
    //=================== End  =========================


}
