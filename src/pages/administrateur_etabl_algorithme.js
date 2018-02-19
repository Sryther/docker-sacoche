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

// jQuery !
$(document).ready
(
  function()
  {

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de méthode -> desactiver les limites autorisées suivant les cas
// ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Tableaux utilisés pour savoir quelles options desactiver
    var tableau_limites_autorisees = new Array();
    tableau_limites_autorisees['geometrique']  = '.1.2.3.4.5.';
    tableau_limites_autorisees['arithmetique'] = '.1.2.3.4.5.6.7.8.9.';
    tableau_limites_autorisees['classique']    = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['bestof1']      = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['bestof2']      =   '.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['bestof3']      =     '.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['frequencemin'] = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    tableau_limites_autorisees['frequencemax'] = '.1.2.3.4.5.6.7.8.9.10.15.20.30.40.50.0.';
    // La fonction qui s'en occupe
    var actualiser_select_limite = function()
    {
      // Déterminer s'il faut modifier l'option sélectionnée
      limite_valeur = $('#f_limite option:selected').val();
      findme = '.'+limite_valeur+'.';
      methode_valeur = $('#f_methode option:selected').val();
      chaine_autorisee = tableau_limites_autorisees[methode_valeur];
      modifier_limite_selected = (chaine_autorisee.indexOf(findme)==-1) ? true : false ; // 1|3 Si true alors il faudra changer le selected actuel qui ne sera plus dans les nouveaux choix.
      if(modifier_limite_selected)
      {
        modifier_limite_selected = chaine_autorisee.substr(chaine_autorisee.length-2,1) ; // 2|3 On prendra alors la valeur maximale dans les nouveaux choix.
      }
      $("#f_limite option").each
      (
        function()
        {
          // On boucle pour activer / desactiver les options du select.
          limite_valeur = $(this).val();
          findme = '.'+limite_valeur+'.';
          if(chaine_autorisee.indexOf(findme)==-1)
          {
            $(this).prop('disabled',true);
          }
          else
          {
            $(this).prop('disabled',false);
          }
          if(limite_valeur===modifier_limite_selected) // === pour éviter un (false==0) qui sélectionne la 1ère option...
          {
            $(this).prop('selected',true); // 3|3 C'est ici que le selected se fait.
          }
        }
      );
    };
    // Appel de la fonction au chargement de la page puis à chaque changement de méthode
    if( $('#form_input').length ) // Indéfini si pas de droit d'accès à cette fonctionnalité.
    {
      actualiser_select_limite();
    }
    $('#f_methode').change( actualiser_select_limite );

    // Demande de soumission du formulaire
    $('#calculer').click
    (
      function()
      {
        $('#f_action').val('calculer');
        formulaire.submit();
      }
    );
    $('#enregistrer').click
    (
      function()
      {
        $('#f_action').val('enregistrer');
        formulaire.submit();
      }
    );

    // Demande d'initialisation du formulaire avec les valeurs de l'établissement
    // Un simple boutton de type "reset" ne peut être utilisé en cas d'enregistrement en cours de procédure
    $('#initialiser_etablissement').click
    (
      function()
      {
        for ( var key in tab_select )
        {
          $('#'+key+' option[value='+tab_select[key]+']').prop('selected',true);
        }
        for ( var key in tab_valeur )
        {
          $('#'+key).val(tab_valeur[key]);
        }
        for ( var key in tab_seuil )
        {
          $('#'+key).val(tab_seuil[key]);
        }
        actualiser_select_limite();
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_input");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_methode    : { required:true },
          f_limite     : { required:true },
          f_retroactif : { required:true }
        },
        messages :
        {
          f_methode    : { required:"méthode requise" },
          f_limite     : { required:"méthode requise" },
          f_retroactif : { required:"méthode requise" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
        // success: function(label) {label.text("ok").attr('class','valide');} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg",
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        // Vérification valeurs des codes
        var val_min = -1 ;
        var nb_sup_100 = 0 ;
        for ( var key in tab_valeur )
        {
          var saisie = $('#'+key).val();
          var valeur = parseInt(saisie,10);
          if( isNaN(saisie) || ( parseFloat(saisie) != valeur ) )
          {
            $('#ajax_msg').attr('class','erreur').html("Valeur d'un code : valeurs entières requises.").show();
            $('#'+key).focus();
            return false;
          }
          else if( valeur < 0 )
          {
            $('#ajax_msg').attr('class','erreur').html("Valeur d'un code : valeur positives requises.").show();
            $('#'+key).focus();
            return false;
          }
          else if( valeur <= val_min )
          {
            $('#ajax_msg').attr('class','erreur').html("Valeur d'un code : valeurs croissantes requises.").show();
            $('#'+key).focus();
            return false;
          }
          else if( valeur > 100 )
          {
            nb_sup_100++;
          }
          val_min = valeur;
        }
        if( nb_sup_100 >= 2 )
        {
          $('#ajax_msg').attr('class','erreur').html("Valeur d'un code : une seule valeur dépassant 100 permise.").show();
          $('#'+key).focus();
          return false;
        }
        else if( val_min > 200 )
        {
          $('#ajax_msg').attr('class','erreur').html("Valeur d'un code : 200 maximum pour le meilleur code.").show();
          $('#'+key).focus();
          return false;
        }
        // Vérification valeurs seuils
        var val_min = -1 ;
        for ( var key in tab_seuil )
        {
          var saisie = $('#'+key).val();
          var valeur = parseInt(saisie,10);
          if( isNaN(saisie) || ( parseFloat(saisie) != valeur ) )
          {
            $('#ajax_msg').attr('class','erreur').html("Seuil d'acquisition : valeurs entières requises.").show();
            $('#'+key).focus();
            return false;
          }
          else if( ( val_min==-1 ) && ( valeur != 0 ) )
          {
            $('#ajax_msg').attr('class','erreur').html("Seuil d'acquisition : valeur minimale requise à 0.").show();
            $('#'+key).focus();
            return false;
          }
          else if( valeur <= val_min )
          {
            $('#ajax_msg').attr('class','erreur').html("Seuil d'acquisition : valeurs croissantes requises.").show();
            $('#'+key).focus();
            return false;
          }
          else if( ( key.substring(2)=='min' ) && ( valeur != val_min+1 ) )
          {
            $('#ajax_msg').attr('class','erreur').html("Seuil d'acquisition : intervalles consécutifs requis.").show();
            $('#'+key).focus();
            return false;
          }
          val_min = valeur;
        }
        if( val_min != 100 )
        {
          $('#ajax_msg').attr('class','erreur').html("Seuil d'acquisition : valeur maximale requise à 100.").show();
          $('#'+key).focus();
          return false;
        }
      }
      if( $('#f_action').val()=='calculer' )
      {
        $('#bilan table tbody').hide();
      }
      $('button').prop('disabled',true);
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;").show();
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('button').prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $('button').prop('disabled',false);
      var f_action = $('#f_action').val();
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else if(f_action=='calculer')
      {
        $('#ajax_msg').attr('class','valide').html("Simulation effectuée !");
        $('#bilan table tbody').html(responseJSON['value']).show();
      }
      else if(f_action=='enregistrer')
      {
        eval(responseJSON['value']);
        $('#ajax_msg').attr('class','valide').html("Valeurs mémorisées !");
      }
    }

    // Initialisation
    formulaire.submit();

  }
);
