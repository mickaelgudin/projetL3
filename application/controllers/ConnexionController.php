<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Ce controller est le controller gérant la partie
 * connexion pour les etudiants et la professeur
 *
 * @author Mickael GUDIN <mickaelgudin@gmail.com>
 */
class ConnexionController extends CI_Controller
{

    private $_cookie = array(
        'expire' => '86500',
        'path' => '/'
    );

    private $passField = 'password';
    private $emailField = 'email';
    
    private $_cookiesId = array(
        "name" => "189CDS8CSDC98JCPDSCDSCDSCDSD8C9SD",
        "password" => "1C89DS7CDS8CD89CSD7CSDDSVDSIJPIOCDS"
    );
    
    private $prefixCookie = 'cookie_prefix';
    

    /**
     * Charge les fonctions utilisees
     */
    function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('cookie');
        $this->load->library('encrypt');
        $this->load->helper('form');
        /*
         * MODEL USER PERMETTANT DE VERIFIER SI LES IDENTIFIANTS
         * CORRESPONDENT A UN ETUDIANT OU A LA PROFESSEUR
         */
        $this->load->model('dao/UserModel');
    }

    /**
     * Charge la vue connexion
     *
     * @return void
     */
    public function index()
    {
        $this->load->view('connexion');
    }

    /**
     * permet de verifier les identifiants
     * et de donner acces à
     * l'enseignante ou à un etudiant(via des cookies)
     * uniquement si les identifiants sont
     * correct
     *
     * @uses $this->setCookieForUser
     * @uses $this->redirect()
     * @uses get_cookie()
     * @uses $this->UserModel->validate()
     *      
     * @return void
     */
    public function connexion()
    {
        if ($this->input->post($this->emailField, TRUE) && $this->input->post($this->passField, TRUE)) {
            
            if ($this->UserModel->validate($this->input->post($this->emailField), $this->input->post($this->passField))) {
                /* --ON INITIALISE LES COOKIES-- */
                $this->setCookieForUser('name', $this->input->post($this->emailField));
                $this->setCookieForUser($this->passField, $this->input->post($this->passField));
                /* ----------------------------- */

                $this->redirect(false, "accueil");
            } else {
                $this->redirect();
            }
        } elseif (get_cookie($this->config->item($this->prefixCookie) . $this->_cookiesId['name'], TRUE) && get_cookie($this->config->item($this->prefixCookie) . $this->_cookiesId[$this->passField], TRUE)) {
            
            $mail = $this->encrypt->decode(get_cookie($this->config->item($this->prefixCookie) . $this->_cookiesId['name']));
            $password = $this->encrypt->decode(get_cookie($this->config->item($this->prefixCookie) . $this->_cookiesId[$this->passField]));

            $hasFailed = false;
            if (!($this->UserModel->validate($mail, $password))){
                $hasFailed = true;
            }
        } 
        
        $this->redirect();
    }

    /**
     * permet de rediriger l'user vers
     * une page si la connexion a échouée
     * l'user est renvoye à la page de
     * connexion
     *
     * @param boolean $hasFailed
     * @param string $url
     *
     * @uses $this->session->set_flashdata()
     * @uses redirect()
     *      
     * @return void
     */
    private function redirect($hasFailed = true, $url = "connexion")
    {
        if ($hasFailed){
            $this->session->set_flashdata("unable_to_connect", "La connexion a échouée");
        }
        redirect(site_url($url));
    }

    /**
     * function pour creer un cookie pour
     * soit un enseignant ou un eleve
     * (seul le prefix change)
     *
     * @param string $typeCookie
     * @param string $inputValue
     *
     * @return void
     */
    private function setCookieForUser(string $typeCookie, string $inputValue)
    {
        $cookie = $this->_cookie;
        $cookieId = $this->_cookiesId;
        $cookie['name'] = $cookieId[$typeCookie];
        $cookie['value'] = $this->encrypt->encode($inputValue);

        $cookie['prefix'] = ($this->UserModel->type === "enseignant") ? $this->config->item($this->prefixCookie) : "ux_e";
        set_cookie($cookie);
    }
}