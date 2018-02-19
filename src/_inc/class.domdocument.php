<?php
/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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

// http://fr2.php.net/manual/fr/domdocument.validate.php#85792

class MyDOMDocument
{
  private $_delegate;
  private $_validationErrors;

  public function __construct (DOMDocument $pDocument)
  {
    $this->_delegate = $pDocument;
    $this->_validationErrors = array();
  }

  public function __call ($pMethodName, $pArgs)
  {
    if ($pMethodName == "validate")
    {
      $eh = set_error_handler(array($this, "onValidateError"));
      $rv = $this->_delegate->validate();
      if ($eh)
      {
        set_error_handler($eh);
      }
      return $rv;
    }
    else
    {
      return call_user_func_array(array($this->_delegate, $pMethodName), $pArgs);
    }
  }

  public function __get ($pMemberName)
  {
    if ($pMemberName == "errors")
    {
      return $this->_validationErrors;
    }
    else
    {
      return $this->_delegate->$pMemberName;
    }
  }

  public function __set ($pMemberName, $pValue)
  {
    $this->_delegate->$pMemberName = $pValue;
  }

  public function onValidateError ($pNo, $pString, $pFile = null, $pLine = null, $pContext = null)
  {
    $this->_validationErrors[] = preg_replace("/^.+: */", "", $pString);
  }
}

?>