<?php
/**
 * @version $Id$
 * @author Daniel Caillibaud <daniel.caillibaud@sesamath.net>
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

/**
 * MAILS
 *
 * Après avoir pas mal fouillé et lu une tonne de doc, faut se passer d'accents
 * dans les headers (sujet compris) ou bien utiliser une classe externe correcte
 * (y'en a pas 50, et ZF2/Mail ou Symfony, pour faire ça c'est un peu délirant, donc
 * on se prend un peu la tête pour faire du mail txt propre !
 *
 * Cf pas mal d'explications en commentaires à la fin
 */

/**
 * Une classe pour envoyer des mails en texte brut correctement encodés (avec accents
 * dans le sujet ou les noms de destinataire / expéditeur).
 * Les destinataires peuvent être des listes (tableaux ou chaînes avec la virgule en séparateur).
 * Chacun pouvant être "nom <adresse>", "<adresse>" ou "adresse".
 */
class Sesamail
{

  /**
   * Le charset utilisé (UTF-8, exception sinon, faudra venir coder une méthode setEncoding
   * et tester de manière approfondie le jour où y'aura besoin d'autre chose)
   * @var string
   */
  protected $charset;

  /**
   * Sujet (encodé $charset quoted-printable)
   * @var string
   */
  protected $subject;

  /**
   * Application
   * @var string 
   */
  protected $domain_application;

  /**
   * Destinataire(s) (encodés $charset quoted-printable)
   * @var string
   */
  protected $to;

  /**
   * Le message complet ($charset)
   * @var string
   */
  protected $message;

  /**
   * Tous les autres headers (encodés $charset quoted-printable)
   * On utilise une chaîne (et pas un tableau) car la valeur dépend de la clé (le retour chariot éventuel)
   * et que l'on utilise mb_encode_mimeheader qui travaille sur toute la ligne (clé comprise)
   * @var string
   */
  protected $headers = '';

  /**
   * L'expéditeur par défaut (Sender)
   * @var string
   */
  protected $default_sender = NULL;

  /**
   * Le destinataire de la réponse par défaut (ReplyTo)
   * @var string
   */
  protected $default_replyto = NULL;

  /**
   * Pour ne pas ajouter plusieurs fois nos headers par défaut
   * @var boolean
   */
  protected $default_headers_added = FALSE;

  /**
   * Pour ne pas vérifier les adresses des destinataires
   * (et permettre l'usage d'alias locaux autres que /etc/aliases)
   */
  protected $no_check_recipients = FALSE;

  /**
   * Constructeur
   * @throws Exception
   */
  function __construct()
  {
    // le charset
    // si on veut autre chose que de l'UTF-8 faudra coder une méthode $this->setEncoding
    // On lance une exception pour être sûr que qqun viendra mettre son nez ici
    $internal_encoding = mb_internal_encoding();
    if ($internal_encoding != 'UTF-8') {
      throw new Exception("Cette classe Mail ne fonctionne qu'avec un mb_internal_encoding en UTF-8 (ici on a $internal_encoding)");
    }
    $this->charset = $internal_encoding;

    // notre End Of Header Line
    if (!defined('EOHL')) {
      // Les spécifications précisent "\r\n", mais ça pose pb avec certains serveurs/clients (laposte.net par ex)
      // donc on fait un truc pas standard, que les bons serveurs savent corriger, et que les mauvais pensent être la norme :-/
      define('EOHL', "\n");
    }

    // le Sender par défaut
    if( defined('HEBERGEUR_MAILBOX_BOUNCE') && HEBERGEUR_MAILBOX_BOUNCE ) {
      // Si le webmestre a renseigné une adresse de bounce
      $this->default_sender = HEBERGEUR_MAILBOX_BOUNCE;
    }
    else if( defined('WEBMESTRE_COURRIEL') && WEBMESTRE_COURRIEL ) {
      // Si le webmestre n'a pas renseigné d'adresse de bounce
      $this->default_sender = WEBMESTRE_COURRIEL;
    }
    else {
      // Pour les pages qui restent où on n'a pas chargé les constantes de l'installation (portail du projet par exemple) 
      $this->default_sender = 'bounces@sesamath.net';
    }

    // le ReplyTo par défaut
    if(defined('MAIL_SACOCHE_CONTACT')) {
      $this->default_replyto = 'Contact SACoche <'.MAIL_SACOCHE_CONTACT.'>';
    }
    else if(defined('WEBMESTRE_COURRIEL')) {
      $this->default_replyto = WEBMESTRE_PRENOM.' '.WEBMESTRE_NOM.' <'.WEBMESTRE_COURRIEL.'>';
    }

    // Application : utilisé en préfixe du sujet et en nom d'expéditeur par défaut
    if(defined('HEBERGEUR_DENOMINATION')) {
      $this->domain_application = !empty($_SESSION['BASE']) ? 'SACoche '.HEBERGEUR_DENOMINATION.' '.$_SESSION['BASE'] : 'SACoche '.HEBERGEUR_DENOMINATION ;
    }
    else {
      $this->domain_application = 'SACoche' ;
    }

    // Les headers communs à tous nos mails
    $this->headers .= 'Sender: <'.$this->default_sender.'>'.EOHL;

    // Les autres headers communs à tous nos mails sont ajoutés dans le send pour qu'ils soient en dernier
  }
  // __construct

  /**
   * Une remplaçante de la fonction mail classique de php (ATTENTION, pour les 3 premiers arguments seulement)
   * @param string|array $to        Le ou les destinataire(s) ; adresse seule ou "Nom en UTF-8 <adresse@domaine.tld>" dans un tableau ou une chaine à virgules si plusieurs destinataires
   * @param string       $subject   L'objet du mail (UTF-8)
   * @param string       $message   Le message texte à envoyer (UTF-8)
   * @param string       $replyto   Facultatif, même format que $to 
   * @param string       $from      Facultatif, l'intitulé de l'expéditeur (ou intitulé + adresse) ; adresse surchargée par $default_sender
   * @param array        $args_sup  Les headers supplémentaires, sous la forme array(header_key => header_value)
   * @param array        $options   D'éventuelles options (no_check_recipients peut être mis à true)
   * @return boolean                Le résultat de la fonction mail de php: TRUE si le mail a été accepté pour livraison, FALSE sinon.
   */
  public static function mail($to, $subject, $message, $replyto = '', $from = '', $args_sup = array(), $options = array())
  {
    $mail = new Sesamail;

    // L'option no_check_recipient en 1er
    if (!empty($options['no_check_recipients'])) {
      $mail->no_check_recipients = TRUE;
    }

    // L'ordre d'affectation des headers n'impacte pas celui dans lequel la fonction mail de php les ordonnera au final.
    // Dommage car mettre le sujet en dernier permettrait d'interpréter les autres headers quand windows mail tronque le sujet.

    // To
    if (empty($to)) {
      trigger_error("Aucun destinataire spécifié");
      return FALSE;
    }
    else if (!$mail->setTo($to)) {
      trigger_error("Fonction mail() appellée avec un destinataire invalide ($to), mail non envoyé.");
      return FALSE;
    }

    // Subject
    $subject = ($mail->domain_application) ? '['.$mail->domain_application.'] '.$subject : $subject ;
    $mail->setSubject($subject);

    // Message
    $mail->setMessage($message);

    // ReplyTo ; on ne peut pas forcer l'adresse du destinataire en ReplyTo, cela peut être utilisé pour spammer et peut conduire à un rejet du mail...
    $replyto = ( empty($replyto) && !is_null($replyto) && !empty($mail->default_replyto)) ? $mail->default_replyto : $replyto ;
    if($replyto)
    {
      $mail->setReplyTo($replyto);
    }

    // From
    $from = (!empty($from)) ? $from : $mail->domain_application ;
    $mail->setFrom($from);

    // et les args Sup
    foreach ($args_sup as $header_key => $header_value) {
      // on liste pas les headers avec destinataire, on regarde juste chaine avec @ ou pas
      if (is_string($header_value) && strpos($header_value, '@')) { // @ en 1er caractère pas géré comme un vrai destinataire
        $mail->setRecipients($header_key, $header_value);
      }
      elseif (is_array($header_value)) { // setRecipients se débrouille
        $mail->setRecipients($header_key, $header_value);
      }
      else {
        $mail->setHeader($header_key, $header_value);
      }
    }

    // reste à envoyer
    return $mail->send();
  }
  // mail

  /**
   * Affecte un ou des destinataires
   *
   * On ne vérifie pas la syntaxe de l'adresse mail (car très lourd si on veut le faire correctement,
   * cf http://www.linuxjournal.com/article/9585?page=0,3)
   *
   * @param type $header Le type de destinataire (To, Cc, Bcc, Reply-To...)
   * @param type $recipients Le ou les destinataires (chaine ou tableau)
   *   Peut être sous la forme
   *   - une chaine avec une adresse seule
   *   - une chaine avec un seul destinataire "nom <adresse>"
   *   - une liste, sous forme de chaine séparée par des virgules (mélange des deux précédents formats possible)
   *   - une liste en tableau (avec les éléments sous forme "adresse", "nom <adresse>" ou nom => adresse, panachage possible)
   * @return boolean FALSE si aucun destinataire valide, TRUE sinon
   */
  public function setRecipients($header_key, $recipients)
  {
    // le virer s'il existe déjà (pas gourmand sinon, même pour To)
    $this->removeHeader($header_key);

    // si plusieurs destinataires dans une chaine, on en fait un tableau
    if (is_string($recipients) && strpos($recipients, ',')) {
      $recipients = explode(',', $recipients);
    }

    // si toujours une chaine, c'est un destinataire unique
    if (is_string($recipients)) {
      // c'est un peu idiot d'extraire pour réassembler plus tard, mais
      // sinon les <@> sont encodés par mb_encode_mimeheader
      // et ça valide les valeurs au passage
      list($name, $address) = $this->extractNameAndAddress($recipients);
      if ($address) {
        // adresse mail valide
        $header_value = $this->headerFormat($header_key, $name, $address);
        if ($header_key == 'To') {
          $this->to = $header_value;
        }
        else {
          $this->headers .= $header_value . EOHL;
        }
      }
      else {
        // On trace l'appel
        ob_start();
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = ob_get_contents();
        ob_end_clean();
        trigger_error('Appel de ' .__METHOD__ ."() avec une adresse invalide ($recipients) : ".str_replace("\n", ', ', $trace), E_USER_WARNING);
        return FALSE;
      }
      return TRUE;
    }
    elseif (is_array($recipients)) {
      // tableau avec plusieurs destinataires
      $header = $this->recipientsArrayToHeader($header_key, $recipients); // peut être multilignes
      if (!empty($header)) {
        if ($header_key == 'To') {
          $this->to = $header;
        }
        else {
          $this->headers .= $header . EOHL;
        }
        return TRUE;
      }
    }
    return FALSE;
  }
  // setRecipients

  /**
   * Affecte le header To (wrapper de setRecipients)
   *
   * @param string|array $to   Le ou les destinataire, même format que pour setRecipients
   * @return bool
   */
  public function setTo($to)
  {
    return $this->setRecipients('To', $to);
  }
  // setTo

  /**
   * Affecte le header Reply-To
   *
   * @param string|array $replyto   Le ou les destinataire, même format que pour setRecipients
   * @return void
   */
  public function setReplyTo($replyto)
  {
    $this->setRecipients('Reply-To', $replyto);
  }
  // setReplyTo

  /**
   * Affecte le sujet du mail
   *
   * @param string $subject
   * @return void
   */
  public function setSubject($subject)
  {
    // La version du dépôt Sésamath dégage les accents du sujet car en 10/2012 G.K. avait encore des pbs avec windows mail.
    // Pour le projet SACoche le choix a été fait d'alléger en conservant les accents, je n'ai reçu aucun retour évoquant un pb...
    $this->subject = $this->headerFormat('Subject', $subject);
    // version avec tout sur une ligne   : str_replace('?=' .EOHL .'=?utf-8?Q?', '', [...]);
    // version avec _ à la place des =20 : str_replace('=20', '_', [...]);
  }
  // setSubject

  /**
   * Affecte le message
   *
   * @param string $message
   * @return void
   */
  public function setMessage($message)
  {
    // on s'assure que c'est bien des \r\n dans le message
    $message = str_replace("\r", "",     $message);
    $message = str_replace("\n", "\r\n", $message);
    $this->message = $message;
    // $this->message = quoted_printable_encode($message); // à mettre avec Content-Transfer-Encoding: quoted-printable
  }
  // setMessage

  /**
   * Affecte l'expéditeur
   *
   * L'adresse est imposée par $default_sender
   *
   * @param string $from   nom <adresse> | <adresse> | adresse | nom
   * @return void
   */
  public function setFrom($from)
  {
    if (empty($from)) {
      $name = '';
    }
    else if (strpos($from, '<') !== FALSE) {
      // nom <adresse> | <adresse>
      list($name, $address) = $this->extractNameAndAddress($from);
    }
    else if (strpos($from, '@')) {
      // adresse
      $name = '';
    }
    else {
      // nom
      $name = str_replace(',', '', $from); // gaffe aux virgules, mb_encode_mimeheader les échappe pas
    }
    $address = $this->default_sender;
    $from = $this->headerFormat('From', $name, $address) . EOHL;
    $this->headers .= $from;
  }
  // setFrom

  /**
   * Ajoute un header quelconque (et l'encode correctement).
   *
   * Pour un header avec adresse(s) mail, utiliser setRecipients à la place.
   *
   * @param string $name   Le nom du header (Content-Type par ex)
   * @param string $value  La valeur du header
   */
  public function setHeader($header_key, $header_value)
  {
    $header = $this->headerFormat($header_key, $header_value) . EOHL;
    $this->removeHeader($header_key);
    $this->headers .= $header;
  }
  // setHeader

  /**
   * Vire un header s'il le trouve
   * @param string $header_key Le nom du header
   */
  public function removeHeader($header_key)
  {
    $start = strpos($this->headers, $header_key . ': ');
    if ($start !== FALSE) {
      // y'en avait un, on le vire
      $end = strpos($this->headers, EOHL, $start + 1); // $this->headers se termine par EOHL donc on est sûr d'en trouver un
      $this->headers = substr($this->headers, 0, $start) . substr($this->headers, $end + strlen(EOHL));
    }
  }
  // removeHeader

  /**
   * Envoie le mail
   * @return boolean Le résultat de la fonction mail de php (TRUE si le mail a été accepté pour envoi, FALSE sinon).
   */
  public function send()
  {
    // meth publique, donc on vérifie que au moins le to est valide
    if (empty($this->to)) {
      // faut ajouter plus d'infos si on veut pouvoir l'utiliser (implode pour avoir tout sur une ligne de log)
      ob_start();
      debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
      $trace = ob_get_contents();
      ob_end_clean();
      trigger_error('Appel de ' .__METHOD__ .'() avec un to vide, la trace : '
          .str_replace("\n", ', ', $trace), E_USER_WARNING);
      return FALSE;
    }
    // pour les autres anomalies on ajoute une notice mais on laisse faire
    elseif (empty($this->subject)) {
      trigger_error('Mail avec sujet vide envoyé à ' .$this->to);
    }
    elseif (empty($this->message)) {
      trigger_error('Mail vide envoyé à ' .$this->to);
    }
    return mail($this->to, $this->subject, $this->message, $this->getHeaders());
  }
  // send

  /**
   * Récupère les headers du mail courant et ajoute le from s'il n'y est pas
   * @return string Les headers
   */
  protected function getHeaders()
  {
    // on ajoute le From s'il a pas été mis
    if (strpos($this->headers, 'From: ') === FALSE && !empty($this->default_sender)) {
      $from = $this->headerFormat('From', '' /*name*/ , $this->default_sender) . EOHL;
      $this->headers .= $from;
    }
    // et nos headers par défaut
    $this->addDefaultHeaders();
    return $this->headers;
  }
  // getHeaders

  /**
   * Formate un header d'après un tableau de destinataires (pour To, Cc, Bcc, Reply-To...)
   * @param string $header_key Le nom header (To, Cc ou Bcc)
   * @param array $recipients les destinataires, chacun sous la forme
   *   "adresse" ou nom => adresse ou "nom <adresse>" (à éviter au profit du précédent, car on doit séparer nom et adresse)
   * @return string Le header formaté quoted-printable (et préfixé si c'est pas To)
   */
  protected function recipientsArrayToHeader($header_key, $recipients)
  {
    if (!is_array($recipients)) {
      throw new Exception('Paramètre invalide');
    }
    $header = ''; // la string du header (clé: valeur)
    foreach ($recipients as $name => $address) {
      // name pas forcément la clé, il peut être dans la valeur de l'élément
      if (is_numeric($name)) {
        list($name, $address) = $this->extractNameAndAddress($address);
      }
      if (strpos($address, '@')) {
        if ($header == '') {
          // c'est la 1re valeur, faut $header_key en début (sauf To mais headerFormat le gère)
          $header = $this->headerFormat($header_key, $name, $address);
        }
        else {
          // on cherche pas à savoir si ça peut rentrer sur la dernière ligne, on va à la ligne
          // on met 'To' en $header pour pas le récupérer en retour
          // (si on avait mis xx il aurait fallu virer les "xx: " du début du retour
          $header .= ',' . EOHL . ' ' . $this->headerFormat('To', $name, $address);
        }
      }
      // Sinon, pas une adresse mail valide, on laisse tomber (extractNameAndAddress a déjà râlé dans le log)
    }
    // On se préoccupe pas du préfixe, headerFormat l'a fait au 1er passage
    return $header;
  }
  // recipientsArrayToHeader

  /**
   * Sépare adresse mail et nom d'une chaine de la forme "nom <adresse>", et valide l'adresse
   * Si l'adresse est invalide, loggue l'erreur et renvoie une adresse à FALSE.
   * Si c'était une liste d'adresse séparées par des virgules, ne renvoie que la 1re
   * @param string $string La chaîne "nom <adresse>" ou "adresse" à analyser
   * @return array [$name,$address] ($address sera FALSE si ça passe pas FILTER_VALIDATE_EMAIL)
   */
  protected function extractNameAndAddress($string)
  {
    $name    = '';
    $address = '';
    $start   = strpos($string, '<');
    if ($start === FALSE) {
      $address = $string;
    }
    else {
      // on vire tout séparateur éventuel dans le nom
      $name    = trim(str_replace(',', '', substr($string, 0, $start)));
      $end     = strpos($string, '>');
      $address = ($end) ? substr($string, $start + 1, $end - $start - 1) : substr($string, $start + 1);
    }
    $addressValide = NULL;
    // On tolère les alias locaux de /etc/aliases (pas d'éventuels autres déclarés ailleurs...)
    if (strpos($address, '@') === FALSE && is_readable('/etc/aliases')) {
      $ouput = array();
      exec('grep -c "^' .$address .'[ :]" /etc/aliases', $ouput);
      if ($ouput[0] === "1") {
        $addressValide = $address;
      }
    }
    // si c'est pas un alias valide on filtre (sauf si no_check_recipients)
    if (!$addressValide) {
      $addressValide = $this->no_check_recipients ? $address : filter_var($address, FILTER_VALIDATE_EMAIL);
    }
    // Et si toujours invalide on trace l'appel
    if (!$addressValide) {
      ob_start();
      debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
      $trace = ob_get_contents();
      ob_end_clean();
      trigger_error('Appel de ' .__METHOD__ ."() avec une adresse invalide ($address) : ".str_replace("\n", ', ', $trace), E_USER_WARNING);
    }
    return array($name, $addressValide);
  }
  // extractNameAndAddress

  /**
   * Formate nom & adresse en les validant au passage
   * @param string $name Le nom
   * @param string $address L'adresse
   * @return string La concaténation (FALSE en cas d'adresse invalide)
   */
  protected function concatNameAndAddress($name, $address)
  {
    $str = '';
    if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
      if ($name) {
        // on pourrait mettre le nom entre guillemets et laisser les virgules... mais faut alors aussi gérer les guillemets.
        $str = trim(str_replace(',', '', $name)) . " <$address>";
      }
      else {
        $str = $address;
      }
    }
    else {
      trigger_error("Adresse '$address' invalide");
      $str = FALSE;
    }
    return $str;
  }
  // concatNameAndAddress

  /**
   * Encode un header et retourne la chaîne correspondante préfixée (sauf To et Subject), (FALSE si adresse fournie et invalide)
   *
   * @param string $header_key Le nom du header (To, Subject, From, Reply-To, etc.)
   * @param string $str La valeur du header (la chaîne à encoder comme le nom d'un destinataire ou le sujet)
   * @param string $address L'éventuelle adresse mail, à passer séparément pour ne pas encoder <@>
   * @return string Le header formaté et préfixé (sauf To et Subject) sans retour chariot de fin (FALSE si adresse invalide)
   */
  protected function headerFormat($header_key, $str, $address = '')
  {
    // On vire les espaces éventuels (et ça plantera si c'est pas des strings)
    $str = trim($str);
    $address = trim($address);

    // On ajoute toujours le header en préfixe, sauf To ou Subject
    // car la fonction mail de php ne veut que la valeur (sans "clé: " en début de ligne)
    if ($header_key == 'To' || $header_key == 'Subject') {
      // faut un offset pour rester sous les 74 caractères quand la fonction mail de php aura ajouté
      // le header dans le source envoyé à sendmail (+2 pour le ": " ajouté)
      $enc_str = mb_encode_mimeheader($str, $this->charset, "Q", EOHL, strlen($header_key) + strlen(EOHL));
    }
    else { // nom du header au début
      if ($str != '') {
        // si on laissait mb_encode_mimeheader il virerait l'espace de fin et
        // l'adresse se retrouverait ensuite collée au ":"
        $enc_str = mb_encode_mimeheader($header_key . ': ' . $str, $this->charset, "Q", EOHL);
      }
      else {
        $enc_str = $header_key . ': ';
      }
    }

    // La chaine est correctement encodée (et préfixée par "$header_key:" si besoin), on regarde l'adresse mail
    if ($address != '') {
      if ($this->no_check_recipients || filter_var($address, FILTER_VALIDATE_EMAIL)) {
        if (!empty($str)) {
          // faut ajouter une espace
          $enc_str .= ' ';
          // Et les chevrons dans l'adresse (s'ils sont pas déjà là)
          if (strpos($address, '<') === FALSE) {
            $address = '<' . $address . '>';
          }
        }
        // et on concatène tout ça, mais faut regarder si on a la place sur la dernière ligne
        // on peut pas utiliser le modulo car les lignes font pas forcément toutes la même taille
        $lines = explode(EOHL, $enc_str);
        $reste = 74 - strlen(array_pop($lines));
        // ça rentre ?
        if ($reste < strlen($address)) {
          // pas la place, faut sauter une ligne et ajouter deux espaces
          $enc_str .= EOHL . ' ';
        }
        $enc_str .= $address;
      }
      else {
        trigger_error("Adresse $address invalide", E_USER_WARNING);
        return FALSE;
      }
    }
    elseif ($header_key == 'To' || $header_key == 'Cc' || $header_key == 'Bcc') {
      // adresse imposée pour ceux-là
      throw new Exception("Impossible d'ajouter un header $header_key sans destinataire");
    }

    // La fonction mb_encode_mimeheader() revoie une chaine contenant "UTF-8" qui pose peut-être pb
    // (tous les courrielleurs et lib étudiés mettent utf-8)
    // Et lui donner utf-8 comme paramètre n'y change rien, d'où ce traitement à posteriori avec str_replace().
    return str_replace('UTF-8', 'utf-8', $enc_str);
    // return str_replace( mb_internal_encoding() , strtolower(mb_internal_encoding()) , $enc_str ); // mettre ça le jour ou c'est plus utf-8 only
  }
  // headerFormat (function headerFormatOld avec quoted_printable_encode à la place de mb_encode_mimeheader dans la rev129)

  /**
   * Ajoute les headers Mime-Version, Content-type et Content-Transfer-Encoding
   * Ne le fera qu'une fois même avec plusieurs appels.
   * @staticvar boolean $done Pour ne pas les ajouter plusieurs fois
   */
  protected function addDefaultHeaders()
  {
    if (!$this->default_headers_added) {
      // Les headers communs à tous nos mails, ajoutés dans le send si pas fait avant
      $this->headers .= 'Mime-Version: 1.0' . EOHL;
      $this->headers .= 'Content-type: text/plain; charset=' . $this->charset . EOHL;
      $this->headers .= 'Content-Transfer-Encoding: 8bit' . EOHL;
      //$this->headers .= 'Content-Transfer-Encoding: quoted-printable' .EOHL;
      $this->default_headers_added = TRUE;
    }
  }
  // addDefaultHeaders




  /**
   * Renvoie une URL pour un lien profond vers un espace identifié.
   * 
   * @param string   $query_string   sans "?" initial ni de param de base ou de sso
   * @return string
   */
  public static function adresse_lien_profond($get_param)
  {
    // GET à ajouter pour le type de connexion et le numéro de la base (si besoin)
    if(HEBERGEUR_INSTALLATION=='multi-structures')
    {
      $get_connexion = ($_SESSION['CONNEXION_MODE']!='normal') ? 'sso='.$_SESSION['BASE'].'&' : 'base='.$_SESSION['BASE'].'&' ;
    }
    else
    {
      $get_connexion = ($_SESSION['CONNEXION_MODE']!='normal') ? 'sso&' : '' ;
    }
    // assemblage selon le mode de connexion
    if($_SESSION['CONNEXION_MODE']!='shibboleth')
    {
       // SACoche gère la mémorisation de la page demandée en utilisant $_SESSION['MEMO_GET'].
      $query_string = '?'.$get_connexion.$get_param;
    }
    else
    {
       // Le "maquillage" dans un memoget est obligatoire sur le serveur de Bordeaux,
       // sinon (transmission du param "sso") le serveur shibbolisé redirige vers l'authentification
       // avant que PHP ne prenne la main pour enregistrer les valeurs GET que le service d'authentification externe perd
       // (bug lors de l'appel d'un IdP de type RSA FIM, application nationale du ministère...)
       // Dans ce cas, SACoche utilise un ccokie : @see Cookie::save_get_and_exit_reload() + Cookie::load_get()
      $query_string = '?memoget='.urlencode($get_connexion.$get_param);
    }
    // retour
    return URL_DIR_SACOCHE.$query_string;
  }

  /**
   * Renvoie un texte comportant divers éléments pour la fin du courriel.
   * 
   * @param array   $tab_elements   peut contenir les valeurs 'excuses_derangement' , 'activation_compte' , 'info_connexion' , 'no_reply' ,  'notif_individuelle' , 'signature'
   * @param string  $courriel       facultatif, seulement requis pour 'excuses_derangement' & 'activation_compte' & 'notif_individuelle'
   * @return string
   */
  public static function texte_pied_courriel( $tab_elements , $courriel=NULL )
  {
    $texte = '';
    // texte s'excusant en cas de réception d'un courriel non sollicité
    if(in_array( 'excuses_derangement' , $tab_elements ))
    {
      $texte .= "\r\n";
      $texte .= 'Si vous n\'êtes pas à l\'origine de cette demande, alors quelqu\'un a saisi votre adresse ('.$courriel.') par erreur !'."\r\n";
      $texte .= 'Dans ce cas, désolé pour le dérangement, veuillez ignorer ce message.'."\r\n";
    }
    // texte donnant des informations sur la connexion internet utilisée
    if(in_array( 'info_connexion' , $tab_elements ))
    {
      $AdresseIP = Session::get_IP();
      $HostName  = gethostbyaddr($AdresseIP); // Quand gethostbyaddr() n'arrive pas à déterminer le nom d'hôte (pas de Reverse DNS enregistré), il renvoie l'adresse IP au bout de 15 secondes ! Cela pose problème si le timeout de PHP ou d'un appel cURL est inférieur...
      $UserAgent = Session::get_UserAgent();
      $texte .= "\r\n";
      $texte .= 'Voici, pour information, les informations relatives à la connexion internet utilisée :'."\r\n";
      $texte .= 'Adresse IP --> '.$AdresseIP."\r\n";
      $texte .= 'Nom d\'hôte --> '.$HostName."\r\n";
      $texte .= 'Navigateur --> '.$UserAgent."\r\n";
    }
    // texte avec l'indication pour activer son compte (envoi forcé d'une notif à un compte non activé)
    if(in_array( 'activation_compte' , $tab_elements ))
    {
      $texte .= "\r\n";
      $texte .= 'Remarque : le compte SACoche nécessaire pour accéder au lien ci-dessus n\'a jamais été utilisé.'."\r\n";
      $texte .= 'Si vous n\'en connaissez pas les identifiants, vous pouvez en obtenir via ce formulaire :'."\r\n".URL_DIR_SACOCHE.'?'.'base=' .$_SESSION['BASE'].'&profil=structure&page=public_identifiants_perdus&courriel='.$courriel."\r\n";
    }
    // texte indiquant qu'il ne faut pas répondre à l'envoyeur
    if(in_array( 'no_reply' , $tab_elements ))
    {
      $texte .= "\r\n";
      $texte .= '______________________________________________________________________'."\r\n";
      $texte .= "\r\n";
      $texte .= 'L\'expéditeur de ce courriel est une machine, merci de NE PAS répondre au message.'."\r\n";
    }
    // texte avec l'indication pour modifier ses abonnements et un lien pour signaler une réception anormale
    if(in_array( 'notif_individuelle' , $tab_elements ))
    {
      $texte .= "\r\n";
      $texte .= 'Modifier vos abonnements :'   ."\r\n".Sesamail::adresse_lien_profond('page=compte_email')."\r\n";
      $texte .= 'Consulter vos notifications :'."\r\n".Sesamail::adresse_lien_profond('page=consultation_notifications')."\r\n";
      $texte .= 'Signaler un courriel erroné :'."\r\n".URL_DIR_SACOCHE.'?'.'base=' .$_SESSION['BASE'].'&page=public_contact_admin&courriel='.$courriel."\r\n";
    }
    if(in_array( 'activation_compte' , $tab_elements ))
    {
      $texte .= "\r\n";
      $texte .= 'Signaler un courriel erroné :'."\r\n".URL_DIR_SACOCHE.'?'.'base=' .$_SESSION['BASE'].'&page=public_contact_admin&courriel='.$courriel."\r\n";
    }
    // texte avec la signature "SACoche"
    if(in_array( 'signature' , $tab_elements ))
    {
      $texte .= "\r\n";
      $texte .= '--'."\r\n";
      $texte .= 'SACoche - '.HEBERGEUR_DENOMINATION."\r\n";
    }
    // retour du contenu
    return $texte;
  }

  /**
   * Envoyer ou rendre disponibles les notifications en attente.
   * 
   * @param void
   * @return void
   */
  public static function envoyer_notifications()
  {
    $DB_TAB = DB_STRUCTURE_NOTIFICATION::DB_lister_notifications_a_publier();
    if(!empty($DB_TAB))
    {
      foreach($DB_TAB as $DB_ROW)
      {
        if(!($DB_ROW['jointure_mode']))
        {
          // Notification envoyée de force pour la mise à disposition d'un bilan officiel
          if( (COURRIEL_NOTIFICATION=='non') || (!$DB_ROW['user_email']) )
          {
            // Pas d'envoi autorisé ou pas de mail -> donc pas possible
            DB_STRUCTURE_NOTIFICATION::DB_supprimer_log_id( $DB_ROW['notification_id'] );
          }
          else
          {
            // La notification va pouvoir être envoyée par mail
            $DB_ROW['jointure_mode']='courriel';
          }
        }
        if($DB_ROW['jointure_mode'])
        {
          $notification_statut = ( (COURRIEL_NOTIFICATION=='oui') && ($DB_ROW['jointure_mode']=='courriel') && $DB_ROW['user_email'] ) ? 'envoyée' : 'consultable' ;
          DB_STRUCTURE_NOTIFICATION::DB_modifier_statut( $DB_ROW['notification_id'] , $DB_ROW['user_id'] , $notification_statut );
          if($notification_statut=='envoyée')
          {
            $tab_elements = is_null($DB_ROW['user_connexion_date']) ? array('activation_compte','no_reply','signature') : array('no_reply','notif_individuelle','signature') ;
            $mail_user    = $DB_ROW['user_prenom'].' '.$DB_ROW['user_nom'].' <'.$DB_ROW['user_email'].'>';
            $mail_objet   = 'Notification - '.$DB_ROW['abonnement_objet'];
            $mail_contenu = $DB_ROW['notification_contenu'].Sesamail::texte_pied_courriel( $tab_elements , $DB_ROW['user_email'] );
            $courriel_bilan = Sesamail::mail( $mail_user , $mail_objet , $mail_contenu , NULL );
          }
        }
      }
    }
  }

}
// Class

/**
 * Pourquoi tout ça...
 *
 * Pour les accents dans les en-têtes de mails (sujet, expéditeur...) ;
 * le charset n'a d'effet que sur le corps et les clients de messagerie interprètent
 * différemment le reste (UTF-8 ou ISO-8859-1 etc.).
 *
 * Résultat des courses (plus de détails dans la rev 129 : http://redmine.sesamath.net/projects/commun/repository/revisions/129/entry/Sesamath/Mail.class.php#L255)
 * - mb_send_mail est une cata car
 *   - encode en base64 => spam d'office
 *   - encode pas le To ni les headers sup (From)
 * - iconv_mime_encode pourrait être mieux pour les headers sans adresse mail (sinon il encode le @),
 *   donc le sujet, mais pas de bol il veut le nom du header pour l'ajouter, et pour le sujet on veut
 *   pas du "Subject: " de début pour le filer à la fonction mail. Par ailleurs il ajoute des =?UTF-8?Q?
 *   au mileu des lignes (en plus du début, curieux...)
 * - quoted_printable_encode coupe les lignes mais il met pas le =?UTF-8?Q? au début, du coup
 *   faut recoller pour redécouper autrement
 * - mb_encode_mimeheader est finalement le plus simple
 *
 * => on utilise mb_encode_mimeheader, mais pas sur la partie adresse mail, donc faudra gérer
 * soi-même le wrapping (et virer le : au début quand on lui donne une chaine vide comme nom
 * de header pour To et Subject).
 *
 * \r\n dans les headers ?
 * Les rfc disent que les headers se terminent par \r\n (et une ligne de header trop longue est coupée par "\n  ")
 * mais les client mails microsoft le digèrent pas toujours très bien
 *   http://stackoverflow.com/questions/4415654/which-line-break-in-php-mail-header-r-n-or-n
 *   http://stackoverflow.com/questions/3449431/php-mail-formatting-issue-why-do-crlf-header-line-endings-break-html-email-in/7960957#7960957
 * et apparemment, mettre \n ne pose pas trop de pb aux autres (un comble, vu que c'est MS qui utilise \r\n comme fin
 * de ligne dans les txt alors que unix utilise \n depuis que les fin de lignes existent)
 * Pourtant, http://swiftmailer.org/ utilise \r\n (cf AbstractHeader.php)
 *
 *   * print(mb_encode_mimeheader("un truc évident mais pénible à la longue","UTF-8","Q"));'
un truc =?UTF-8?Q?=C3=83=C2=A9vident=20mais=20p=C3=83=C2=A9nible=20=C3=83?=
 =?UTF-8?Q?=C2=A0=20la=20longue?=
 * print(iconv_mime_encode("From", "un truc évident mais pénible à la longue",
 *   array("scheme" => "Q", "input-charset" => "UTF-8", "output-charset" => "UTF-8")));
From: =?UTF-8?Q?un=20truc=20=C3=A9vident=20ma?==?UTF-8?Q?is=20p?=
 =?UTF-8?Q?=C3=A9nible=20=C3=A0=20la=20longue?=
 * php -r 'print(quoted_printable_encode("From: un truc évident mais pénible à la longue"));'
From: un truc =C3=A9vident mais p=C3=A9nible =C3=A0 la longue
 *
 * Et ensuite, y'a deux écoles
 * "un truc =?UTF-8?Q?=C3=A9vident?="
 * ou
 * "?UTF-8?Q?un=20truc=20=C3=A9vident?="
 *
 * tous les mailers observés utilisent le 1er pour le sujet (idem mb_encode_mimeheader) et
 * le 2e (comme iconv_mime_encode) pour les adresses comme
 * From: ?ISO-8859-1?Q?un=20truc=20=E9vident?= <uneadresse@example.com>
 */