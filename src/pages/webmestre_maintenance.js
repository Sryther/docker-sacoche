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
    // Afficher / masquer le choix du motif du blocage
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_debloquer , #f_bloquer').click
    (
      function()
      {
        if($('#f_bloquer').is(':checked'))
        {
          $('#span_motif').show();
          $('#f_motif').focus();
        }
        else
        {
          $('#span_motif').hide();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Autocompléter le motif du blocage
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    tab_proposition = new Array();
    tab_proposition["rien"]         = "";
    tab_proposition["mise-a-jour"]  = "Mise à jour des fichiers en cours.";
    tab_proposition["maintenance"]  = "Maintenance sur le serveur en cours.";
    tab_proposition["demenagement"] = "Déménagement de l'application en cours.";

    $('#f_proposition').change
    (
      function()
      {
        $('#f_motif').val( tab_proposition[ $(this).val() ] ).focus();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mise à jour des label comparant la version installée et la version disponible
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_label_versions()
    {
      var classe = ( $('#ajax_version_installee').text() == $('#ajax_version_disponible').text() ) ? 'valide' : 'alerte' ;
      $('#ajax_version_installee').attr('class',classe);
    }

    maj_label_versions();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Verrouillage de l'application
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_action : { required:true }
        },
        messages :
        {
          f_action : { required:"choix manquant" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { $('#ajax_msg').html(error); }
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
        $("#bouton_verrouillage").prop('disabled',true);
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $("#bouton_verrouillage").prop('disabled',false);
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      $("#bouton_verrouillage").prop('disabled',false);
      if(responseJSON['statut']==true)
      {
        
        $('#ajax_msg').removeAttr('class').html("");
        $('#ajax_acces_actuel').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Mise à jour automatique des fichiers
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var etape_numero = 0 ;

    function maj_etape(etape_info)
    {
      etape_numero++;
      if(etape_numero==6)
      {
        var version = etape_info['version'];
        var fichier = etape_info['fichier'];
        $('#ajax_maj').attr('class','valide').html('Mise à jour terminée !');
        $('#ajax_version_installee').html(version);
        maj_label_versions();
        $('#bouton_maj').prop('disabled',false);
        $.fancybox( { 'href':fichier , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
        initialiser_compteur();
        return false;
      }
      else
      {
        $('#ajax_maj').attr('class','loader').html('Etape '+etape_numero+' - '+etape_info['value']);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=maj_etape'+etape_numero,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_maj').prop('disabled',false);
              $('#ajax_maj').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              if(responseJSON['statut']==false)
              {
                $('#bouton_maj').prop('disabled',false);
                $('#ajax_maj').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                maj_etape(responseJSON);
              }
            }
          }
        );
      }
    }

    $('#bouton_maj').click
    (
      function()
      {
        etape_numero = 0 ;
        if( $('#ajax_version_installee').text() > $('#ajax_version_disponible').text() )
        {
          $('#ajax_maj').attr('class','erreur').html("Version installée postérieure à la version disponible !");
          return false;
        }
        $('#bouton_maj').prop('disabled',true);
        maj_etape( { "value" : "Récupération de l'archive <em>zip</em>&hellip;" } );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Vérification des fichiers de l'application en place
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function verif_file_appli_etape(etape_info)
    {
      etape_numero++;
      if(etape_numero==6)
      {
        $('#ajax_verif_file_appli').attr('class','valide').html('Vérification terminée !');
        $('#bouton_verif_file_appli').prop('disabled',false);
        $.fancybox( { 'href':etape_info , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
        initialiser_compteur();
        return false;
      }
      $('#ajax_verif_file_appli').attr('class','loader').html('Etape '+etape_numero+' - '+etape_info);
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=verif_file_appli_etape'+etape_numero,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#bouton_verif_file_appli').prop('disabled',false);
            $('#ajax_verif_file_appli').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            if(responseJSON['statut']==false)
            {
              $('#bouton_verif_file_appli').prop('disabled',false);
              $('#ajax_verif_file_appli').attr('class','alerte').html(tab_infos[0]);
              return false;
            }
            else
            {
              verif_file_appli_etape(responseJSON['value']);
            }
          }
        }
      );
    }

    $('#bouton_verif_file_appli').click
    (
      function()
      {
        etape_numero = 0 ;
        $('#bouton_verif_file_appli').prop('disabled',true);
        verif_file_appli_etape("Récupération de l'archive <em>zip</em>&hellip;");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Vérification des dossiers additionnels par établissement
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_verif_dir_etabl').click
    (
      function()
      {
        $('#bouton_verif_dir_etabl').prop('disabled',true);
        $('#ajax_verif_dir_etabl').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=verif_dir_etabl',
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#bouton_verif_dir_etabl').prop('disabled',false);
              $('#ajax_verif_dir_etabl').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              $('#bouton_verif_dir_etabl').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_verif_dir_etabl').attr('class','alerte').html(responseJSON['value']);
                return false;
              }
              else
              {
                var adresse_rapport = responseJSON['value'];
                $('#ajax_verif_dir_etabl').attr('class','valide').html('Vérification terminée !');
                $.fancybox( { 'href':adresse_rapport , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
                initialiser_compteur();
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etapes de maj des bases des établissements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var step_maj = 1;

    function maj_bases_etabl_etape(step_maj)
    {
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=maj_bases_etabl'+'&step_maj='+step_maj,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj_bases_etabl').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus)+' <a id="a_reprise" href="#">Reprendre la procédure.</a>');
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_maj_bases_etabl').attr('class','alerte').html(responseJSON['value']+'<a id="a_reprise" href="#">Reprendre la procédure.</a>');
            }
            else if(responseJSON['value']=='continuer')
            {
              step_maj++;
              $('#ajax_maj_bases_etabl').attr('class','loader').html('Mise à jour en cours : étape ' + step_maj + '...');
              maj_bases_etabl_etape(step_maj);
            }
            else
            {
              var adresse_rapport = responseJSON['value'];
              $.fancybox( { 'href':adresse_rapport , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
              $('#ajax_maj_bases_etabl').attr('class','valide').html('Mise à jour des bases terminée.');
              $('#bouton_maj_bases_etabl').prop('disabled',false);
            }
          }
        }
      );
    }

    $('#bouton_maj_bases_etabl').click
    (
      function()
      {
        $('#bouton_maj_bases_etabl').prop('disabled',true);
        step_maj = 1;
        $('#ajax_maj_bases_etabl').attr('class','loader').html('Mise à jour en cours : initialisation...');
        maj_bases_etabl_etape(step_maj);
      }
    );

    $('#ajax_maj_bases_etabl').on
    (
      'click',
      '#a_reprise',
      function()
      {
        maj_bases_etabl_etape(step_maj);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Etapes de nettoyage des fichiers temporaires des établissements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var step_clean = 1;

    function clean_file_temp_etape(step_clean)
    {
      // Appel en ajax
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=clean_file_temp'+'&step_clean='+step_clean,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_clean_file_temp').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus)+' <a id="a_reprise" href="#">Reprendre la procédure.</a>');
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_clean_file_temp').attr('class','alerte').html(responseJSON['value']+'<a id="a_reprise" href="#">Reprendre la procédure.</a>');
            }
            else if(responseJSON['value']=='continuer')
            {
              step_clean++;
              $('#ajax_clean_file_temp').attr('class','loader').html('Nettoyage en cours : étape ' + step_clean + '...');
              clean_file_temp_etape(step_clean);
            }
            else
            {
              var adresse_rapport = responseJSON['value'];
              $.fancybox( { 'href':adresse_rapport , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
              $('#ajax_clean_file_temp').attr('class','valide').html('Nettoyage des fichiers terminé.');
              $('#bouton_clean_file_temp').prop('disabled',false);
            }
          }
        }
      );
    }

    $('#bouton_clean_file_temp').click
    (
      function()
      {
        $('#bouton_clean_file_temp').prop('disabled',true);
        step_clean = 1;
        $('#ajax_clean_file_temp').attr('class','loader').html('Nettoyage en cours : initialisation...');
        clean_file_temp_etape(step_clean);
      }
    );

    $('#ajax_clean_file_temp').on
    (
      'click',
      '#a_reprise',
      function()
      {
        clean_file_temp_etape(step_clean);
      }
    );

  }
);
