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
// Intercepter la touche entrée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_mode input').keyup
    (
      function(e)
      {
        if(e.which==13)  // touche entrée
        {
          $('#bouton_valider_mode').click();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_mode select , #form_mode input").change
    (
      function()
      {
        $('#ajax_msg_mode').attr('class','alerte').html("Penser à valider les modifications.");
        $('#table_action thead q').attr('class',"ajouter_non").attr("title","Validez d'abord le mode d'identification.");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher / masquer le formulaire CAS
// Afficher / masquer le formulaire GEPI
// Afficher / masquer le formulaire Domaine préfixe
// Afficher / masquer l'adresse de connexion directe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function actualiser_formulaire()
    {
      // on masque
      $('#cas_options , #gepi_options ,#cas_domaine ,#cas_port , #cas_verif , #lien_direct , #lien_gepi , #info_inacheve , #info_hors_sesamath , #info_hors_actualite , #info_hors_ent , #info_heberg_acad , #info_conv_acad , #info_conv_etabl').hide();
      if(!IS_HEBERGEMENT_SESAMATH)
      {
        $('#info_hors_sesamath').show();
      }
      else if(!CONVENTION_ENT_REQUISE)
      {
        $('#info_hors_actualite').show();
      }
      // on récupère les infos
      var valeur = $('#connexion_mode_nom option:selected').val();
      var tab_infos = valeur.split('~');
      var connexion_mode = tab_infos[0];
      var connexion_ref  = tab_infos[1];
      if(connexion_mode=='cas')
      {
        var valeur = tab_param[connexion_mode][connexion_ref];
        var tab_infos = valeur.split(']¤[');
        var type_convention = tab_infos[0];
        var is_domaine_edit = tab_infos[1];
        var is_port_edit    = tab_infos[2];
        var is_operationnel = tab_infos[3];
        var host_subdomain  = tab_infos[4];
        var host_domain     = tab_infos[5];
        var serveur_host    = (host_subdomain!='') ? host_subdomain+'.'+host_domain : host_domain ;
        var serveur_port    = (tab_infos[6]) ? tab_infos[6] : 8443 ;
        $('#cas_serveur_host').val( serveur_host );
        $('#cas_serveur_port').val( tab_infos[6] );
        $('#cas_serveur_root').val( tab_infos[7] );
        $('#cas_serveur_url_login'   ).val( tab_infos[8] );
        $('#cas_serveur_url_logout'  ).val( tab_infos[9] );
        $('#cas_serveur_url_validate').val( tab_infos[10] );
        $('#serveur_host_subdomain').val( host_subdomain );
        $('#serveur_host_domain'   ).val( host_domain );
        $('#serveur_port'          ).val( serveur_port );
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_'+type_convention).show();
        }
        if(connexion_ref=='|perso')
        {
          $('#cas_options').show();
        }
        if(is_domaine_edit=='oui')
        {
          $('#cas_domaine').show();
        }
        if(is_port_edit=='oui')
        {
          $('#cas_port').show();
        }
        if(connexion_mode=='cas')
        {
          $('#cas_verif').show();
        }
        if(is_operationnel=='1')
        {
          $("#bouton_valider_mode").prop('disabled',false);
          $('#lien_direct').show();
        }
        else
        {
          $("#bouton_valider_mode").prop('disabled',true);
          $('#info_inacheve').show();
        }
      }
      else if(connexion_mode=='shibboleth')
      {
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_hors_ent').show();
        }
        var is_operationnel = tab_param[connexion_mode][connexion_ref];
        if(is_operationnel=='1')
        {
          $("#bouton_valider_mode").prop('disabled',false);
          $('#lien_direct').show();
        }
        else
        {
          $("#bouton_valider_mode").prop('disabled',true);
          $('#info_inacheve').show();
        }
      }
      else if(connexion_mode=='gepi')
      {
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_hors_ent').show();
        }
        var valeur = tab_param[connexion_mode][connexion_ref];
        var tab_infos = valeur.split(']¤[');
        $('#gepi_saml_url'   ).val( tab_infos[0] );
        $('#gepi_saml_rne'   ).val( tab_infos[1] );
        $('#gepi_saml_certif').val( tab_infos[2] );
        $("#bouton_valider_mode").prop('disabled',false);
        $('#gepi_options').show();
        $('#lien_direct').show();
        $('#lien_gepi').show();
      }
      else
      {
        if(IS_HEBERGEMENT_SESAMATH && CONVENTION_ENT_REQUISE)
        {
          $('#info_hors_ent').show();
        }
        $("#bouton_valider_mode").prop('disabled',false);
      }
    }

    $("#connexion_mode_nom").change
    (
      function()
      {
        actualiser_formulaire();
      }
    );

    // Initialisation au chargement de la page
    actualiser_formulaire();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mode d'identification (normal, CAS...) & paramètres associés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider_mode').click
    (
      function()
      {
        var connexion_mode_nom = $('#connexion_mode_nom option:selected').val();
        var tab_infos = connexion_mode_nom.split('~');
        var connexion_mode = tab_infos[0];
        var connexion_ref  = tab_infos[1];
        if(connexion_mode=='gepi')
        {
          // Le RNE n'étant pas obligatoire, et pas forcément un vrai RNE dans Gepi (pour les établ sans UAI, c'est un identifiant choisi...), on ne vérifie rien.
          // Pas de vérif particulière de l'empreinte du certificat non plus, ne sachant pas s'il peut y avoir plusieurs formats.
        }
        $("#bouton_valider_mode").prop('disabled',true);
        $('#ajax_msg_mode').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'enregistrer_mode_identification'+'&f_connexion_mode='+connexion_mode+'&f_connexion_ref='+connexion_ref+'&'+$("#form_mode").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#bouton_valider_mode").prop('disabled',false);
              $('#ajax_msg_mode').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("#bouton_valider_mode").prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_mode').attr('class','valide').html("Mode de connexion enregistré !");
                $('#table_action thead q').attr('class',"ajouter").attr("title","Ajouter une convention.");
              }
              else
              {
                $('#ajax_msg_mode').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention : mise en place du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action q.ajouter').click
    (
      function()
      {
        var connexion_mode_texte = $('#connexion_mode_nom option:selected').text();
        $('#f_connexion_texte').val(connexion_mode_texte);
        $('#ajax_msg_ajout').removeAttr('class').html("");
        $('#form_ajout label[generated=true]').removeAttr('class').html("");
        $.fancybox( { 'href':'#form_ajout' , onStart:function(){$('#form_ajout').css("display","block");} , onClosed:function(){$('#form_ajout').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention : fermer le formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_annuler_ajout').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Ajouter une convention : soumettre le formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_valider_ajout').click
    (
      function()
      {
        var connexion_mode_nom = $('#connexion_mode_nom option:selected').val();
        var tab_infos = connexion_mode_nom.split('~');
        var connexion_mode = tab_infos[0];
        var connexion_ref  = tab_infos[1];
        var f_annee = $('#f_annee option:selected').val();
        if(f_annee=='-1')
        {
          $('#ajax_msg_ajout').attr('class','erreur').html("Période manquante !");
          return false;
        }
        $("#form_ajout button").prop('disabled',true);
        $('#ajax_msg_ajout').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'ajouter_convention'+'&f_connexion_mode='+connexion_mode+'&f_connexion_ref='+connexion_ref+'&f_annee='+f_annee,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("#form_ajout button").prop('disabled',false);
              $('#ajax_msg_ajout').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $("#form_ajout button").prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_ajout').attr('class','valide').html("Convention ajoutée !");
                $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
                $('#table_action tbody').prepend(responseJSON['tr']);
                var convention_id = responseJSON['convention_id'];
                var first_time    = 'oui';
                imprimer_documents_convention( convention_id , first_time );
              }
              else
              {
                $('#ajax_msg_ajout').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Imprimer les documents associés à une convention
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click' ,
      'q.voir_archive' ,
      function()
      {
        var convention_id = $(this).parent().parent().attr('id').substring(3);
        var first_time    = 'non';
        imprimer_documents_convention(convention_id,first_time);
      }
    );

    function imprimer_documents_convention(convention_id,first_time)
    {
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+'imprimer_documents'+'&f_convention_id='+convention_id+'&f_first_time='+first_time,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#fichier_contrat').attr("href",responseJSON['fichier_contrat']);
              $('#fichier_facture').attr("href",responseJSON['fichier_facture']);
              $.fancybox( { 'href':'#form_impression' , onStart:function(){$('#form_impression').css("display","block");} , onClosed:function(){$('#form_impression').css("display","none");} , 'centerOnScroll':true } );
            }
            else
            {
              $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
            }
          }
        }
      );
    }

  }
);
