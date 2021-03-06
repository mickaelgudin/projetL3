<?php
defined('BASEPATH') || exit('No direct script access allowed');
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Doctrine\Common\ClassLoader;

require './vendor/autoload.php';
use SMTPValidateEmail\Validator as SmtpEmailValidator;
/**
 * Controller pour la rubrique acces
 * lorsque l'enseignant est connecté
 * il peut importer les eleves d'une
 * classe via cette rubrique
 * @author Mike
 *
 */
class AccesController extends CI_Controller
{
    private $pageAcces = 'acces';
    private $sucessMessageName = 'import_success';

    function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
        $this->load->library('session');
        /* REDIRECT IF CURRENT USER IS NOT ADMIN */
        if ($_SESSION['user'] != 'admin') {
            redirect(site_url("accueil"));
        }

    }

    /**
     * Charge les fonctions utilise pour
     * le formulaire(present dans la vue)
     * puis charge la vue de la page
     *
     * @uses $this->load->helper()
     * @uses $this->load->view()
     *      
     * @return void
     */
    public function index()
    {
        $this->load->helper('cookie');
        $this->load->helper('form');
        $this->session->flashdata($this->sucessMessageName);
        
        $this->load->model("dao/ClasseDAO");
        $data['classeList'] = $this->ClasseDAO->getListClasse();
        $this->load->view('creer_acces', $data);
    }

    /**
     * Charge le fichier excel
     * lit les donnees du fichier excel
     * puis inserer les donnees du fichier excel
     *
     * @uses \PhpOffice\PhpSpreadsheet\IOFactory::identify()
     * @uses \PhpOffice\PhpSpreadsheet\IOFactory::createReader()
     * @uses \PhpOffice\PhpSpreadsheet\IOFactory::createReader()->load()
     * @uses \PhpOffice\PhpSpreadsheet\IOFactory::createReader()->load()->getActiveSheet()
     * @uses \PhpOffice\PhpSpreadsheet\IOFactory::createReader()->load()->getActiveSheet()->toArray()
     *      
     * @return void
     */
    public function import()
    {
        /*
         * Si le fichier n'a pas de nom ou si sont extension n'est pas .xlsx alors on redirige vers la page en
         * affichant un message d'erreur
         */
        $inputFileType = 'Xlsx';
        $inputFileName = $_FILES['file']['tmp_name'];
        /**
         * Idenfication du type de $inputFileName *
         */
        try {
            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
        } catch (Exception $e) {
            $this->session->set_flashdata($this->sucessMessageName, "Veuillez sélectionner un fichier .xlsx");
            redirect(site_url($this->pageAcces));
        }

        /* Creation d'un Reader du type identifiee */
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($inputFileName);
        $eleves = $spreadsheet->getActiveSheet()->toArray();
        
        /*verification que la premiere ligne(entete)
         *corresponde au modèle du fichier
         *d'importation disponible dans la rubrique
         *compte de l'enseignante connecté
         **/
        if($eleves[0][0] != "nom" || $eleves[0][1] != "prenom" || $eleves[0][2] != "email"){
            $this->session->set_flashdata($this->sucessMessageName, "Veuillez vérifier la syntaxe du fichier d'importation.");
            redirect(site_url($this->pageAcces));
        }
        
        $this->doctrine->refreshSchema();

        
        $emailSent = true;
        
        $i = 0;
        foreach ($eleves as $eleve) {
            if ($i > 0 && isset($eleve[0]) && isset($eleve[1]) && isset($eleve[2]) ) {
                    $classe = $this->doctrine->em->find("Classe", $_POST["classe_id"]);
                    $nouvelEleve = new Eleve($eleve[0], $eleve[1], $eleve[2], $classe);
                    /* Le mot de passe sera genere par le helper appelle dans la methode set */
                    $this->load->library('encrypt');
                    $mdpBeforeEncryption = $nouvelEleve->get_random_password();
                    $nouvelEleve->setMotDePasse($this->encrypt->encode($mdpBeforeEncryption));

                    /* On capture les exception afin de ne pas afficher les logs de doctrine à l'utilisateur */
                    try {
                        $this->doctrine->em->persist($nouvelEleve);
                        $this->doctrine->em->flush();
                    } catch (Doctrine\DBAL\DBALException | Doctrine\DBAL\ConnectionException $e) {
                        $this->session->set_flashdata($this->sucessMessageName, "L'importation des élèves a échoué.");
                        redirect(site_url($this->pageAcces));
                        exit();
                    }

                    if (! ($this->send_email_to_students($nouvelEleve->getEmail(), $mdpBeforeEncryption))) {
                        $emailSent = false;
                    }
                
            }
            
            $i++;
        }

        if ($emailSent) {
            $this->session->set_flashdata($this->sucessMessageName, "L'importation des élèves a été effectué.");
        }
        redirect(site_url($this->pageAcces));
    }

    /**
     * function permettant d'envoyer
     * un mail un etudiant en
     * vérifiant qu'on peut lui envoyé
     * un mail
     * @param string $email
     * @param string $mdp
     * @return boolean
     */
    public function send_email_to_students($email, $mdp)
    {
        $validator = new SmtpEmailValidator($email, "tt9814023@gmail.com");
        $results = $validator->validate();
        
        if($results[$email]){
            //si l'email renseigné peut recevoir des mails
            $subject = "Votre inscription sur le site du cours UX a été effectuée";
            $message = "Voici vos identifiants pour vous connecter sur le site du cours UX : <br>  <b>Email : </b>" . $email . "<br>   <b>Mot de passe : </b>" . $mdp;
    
            $params = array(
                $email,
                $subject,
                $message
            );
            $this->load->library('EmailSender', $params);
            
            return true;
        }
        
        return false;
    }
}