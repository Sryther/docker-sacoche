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
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var mode      = false;
    var type      = false;
    var reference = false;
    var f_action   = '';

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:false},1:{sorter:false},2:{sorter:false},3:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0],[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonctions utilisées
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Ajouter ou modifier une configuration : mise en place du formulaire
     * @return void
     */
    var ajouter_modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr  = $(this).parent().parent();
      var objet_tds = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var nom = objet_tds.eq(2).html();
      var id  = objet_tr.attr('id');
      var tab = id.split('_');
      type      = tab[0];
      reference = tab[1];
      // Appel ajax pour charger le formulaire
      $.fancybox( '<label class="loader">Chargement des données...</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+'afficher_'+mode+'&f_type='+type+'&f_reference='+reference+'&f_nom='+encodeURIComponent(nom),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
            }
            else
            {
              $('#zone_tableaux').hide(0);
              $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " une configuration");
              $('#form_contenu').html(responseJSON['value']);
              $('#ajax_msg_gestion').removeAttr('class').html('');
              $('#form_gestion').show(0);
              $.fancybox.close();
            }
          }
        }
      );
    };

    /**
     * Supprimer un niveau partagé ou spécifique : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr  = $(this).parent().parent();
      var objet_tds = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var typ = objet_tds.eq(0).text().substring(1);
      var nom = objet_tds.eq(2).html();
      var id  = objet_tr.attr('id');
      var tab = id.split('_');
      type      = tab[0];
      reference = tab[1];
      // Afficher le formulaire
      $('#zone_tableaux').hide(0);
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " une configuration");
      $('#form_contenu').html('<input id="f_reference" name="f_reference" type="hidden" value="'+reference+'" /><p class="danger">Les classes concernées seront associées à la configuration par défaut !</p><p>Confirmez-vous la suppression de la configuration &laquo;&nbsp;<b>'+typ+' / '+nom+'</b>&nbsp;&raquo; ?</p>');
      $('#ajax_msg_gestion').removeAttr('class').html('');
      $('#form_gestion').show(0);
    };

    /**
     * Annuler une action
     * @return void
     */
    var annuler = function()
    {
      $('#form_gestion').hide(0);
      $('#zone_tableaux').show(0);
      mode = false;
    };

    /**
     * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
     * @return void
     */
    function intercepter(e)
    {
      if(mode)
      {
        if(e.which==13)  // touche entrée
        {
          $('#bouton_valider').click();
        }
        else if(e.which==27)  // touche escape
        {
          $('#bouton_annuler').click();
        }
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire pour valider l'ajout / la modification / la suppression d'une configuration
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var valider_formulaire = function()
    {
      if(mode!='supprimer')
      {
        reference = $('#f_reference').val();
        if(!reference)
        {
          $('#ajax_msg_gestion').attr('class','erreur').html("Indiquer une référence !");
          $('#f_reference').focus();
          return false;
        }
        if(!test_id(reference))
        {
          $('#ajax_msg_gestion').attr('class','erreur').html("La référence ne doit comporter que des chiffres et des lettres (non accentuées) !");
          $('#f_reference').focus();
          return false;
        }
        if(!$('#f_nom').val())
        {
          $('#ajax_msg_gestion').attr('class','erreur').html("Indiquer un nom / commentaire !");
          $('#f_nom').focus();
          return false;
        }
        if( (type=='releve') && (!$('#f_'+type+'_etat_acquisition').is(':checked')) && ($('#f_'+type+'_cases_nb option:selected').val()==0) )
        {
          $('#ajax_msg_gestion').attr('class','erreur').html("Choisir au moins une indication à faire figurer sur le bilan !");
          return false;
        }
        if( ($('#f_'+type+'_check_supplementaire').is(':checked')) && (!$('#f_'+type+'_ligne_supplementaire').val()) )
        {
          $('#ajax_msg_gestion').attr('class','erreur').html("Indiquer le texte de la ligne additionnelle à faire figurer sur le bilan !");
          $('#f_'+type+'_ligne_supplementaire').focus();
          return false;
        }
      }
      $('#bouton_valider').prop('disabled',true);
      $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&f_type='+type+'&'+$('#form_gestion').serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#bouton_valider').prop('disabled',false);
            $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            $('#bouton_valider').prop('disabled',false);
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_gestion').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_msg_gestion').attr('class','valide').html("Demande réalisée !");
              switch (mode)
              {
                case 'ajouter':
                  $('#table_action tbody').append(responseJSON['html']);
                  $('#table_affectation').find('select[name='+type+']').append(responseJSON['option']);
                  break;
                case 'modifier':
                  $('#'+type+'_'+reference).addClass("new").html(responseJSON['html']);
                  $('#table_affectation').find('select[name='+type+']').find('option[value='+reference+']').html(responseJSON['texte']);
                  break;
                case 'supprimer':
                  $('#'+type+'_'+reference).remove();
                  // passer à défaut...
                  $('#table_affectation').find('select[name='+type+']').find('option[value='+reference+']:selected').each
                  (
                    function()
                    {
                      $(this).removeAttr('selected');
                      $(this).parent().find('option[value=defaut]').prop('selected',true);
                    }
                  );
                  $('#table_affectation').find('select[name='+type+']').find('option[value='+reference+']').remove();
                  break;
              }
              tableau_maj();
              $('#form_gestion').hide(0);
              $('#zone_tableaux').show(0);
              mode = false;
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel des fonctions en fonction des événements
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'       , ajouter_modifier );
    $('#table_action').on( 'click' , 'q.modifier'      , ajouter_modifier );
    $('#table_action').on( 'click' , 'q.supprimer'     , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , valider_formulaire );
    $('#form_gestion').on( 'keyup' , 'input,select'    , function(e){intercepter(e);} );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher / masquer des éléments du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_contenu').on(
      'click' ,
      '#f_releve_check_supplementaire' ,
      function()
      {
        $('#f_releve_ligne_factice , #f_releve_ligne_supplementaire').toggle();
        $('#f_releve_ligne_supplementaire').focus();
      }
    );

    $('#form_contenu').on(
      'click' ,
      '#f_bulletin_check_supplementaire' ,
      function()
      {
        $('#f_bulletin_ligne_factice , #f_bulletin_ligne_supplementaire').toggle();
        $('#f_bulletin_ligne_supplementaire').focus();
      }
    );

    $('#form_contenu').on(
      'click' ,
      '#f_releve_etat_acquisition' ,
      function()
      {
        $('#span_releve_etat_acquisition').toggle();
      }
    );

    $('#form_contenu').on(
      'click' ,
      '#f_releve_moyenne_scores , #f_releve_pourcentage_acquis' ,
      function()
      {
        if( ($('#f_releve_moyenne_scores').is(':checked')) || ($('#f_releve_pourcentage_acquis').is(':checked')) )
        {
          $('label[for=f_releve_conversion_sur_20]').show();
        }
        else
        {
          $('label[for=f_releve_conversion_sur_20]').hide();
        }
      }
    );

    $('#form_contenu').on(
      'click' ,
      '#f_bulletin_moyenne_scores' ,
      function()
      {
        if($('#f_bulletin_moyenne_scores').is(':checked'))
        {
          $('#span_moyennes').show();
        }
        else
        {
          $('#span_moyennes').hide();
        }
      }
    );

    $('#form_contenu').on(
      'change' ,
      '#f_bulletin_appreciation_generale_longueur' ,
      function()
      {
        if(parseInt($('#f_bulletin_appreciation_generale_longueur').val(),10)>0)
        {
          $('#span_moyenne_generale').show();
        }
        else
        {
          $('#span_moyenne_generale').hide();
        }
      }
    );

    // relevé report

    $('#form_contenu').on(
      'change' ,
      '#f_releve_appreciation_rubrique_longueur' ,
      function()
      {
        if(parseInt($('#f_releve_appreciation_rubrique_longueur').val(),10)>0)
        {
          $('#span_releve_appreciation_rubrique_report').show();
        }
        else
        {
          $('#span_releve_appreciation_rubrique_report').hide();
        }
      }
    );

    $('#form_contenu').on(
      'change' ,
      '#f_releve_appreciation_generale_longueur' ,
      function()
      {
        if(parseInt($('#f_releve_appreciation_generale_longueur').val(),10)>0)
        {
          $('#span_releve_appreciation_generale_report').show();
        }
        else
        {
          $('#span_releve_appreciation_generale_report').hide();
        }
      }
    );

    // relevé modèle

    $('#form_contenu').on(
      'click' ,
      '#f_releve_appreciation_rubrique_report' ,
      function()
      {
        if($('#f_releve_appreciation_rubrique_report').is(':checked'))
        {
          $('#span_releve_appreciation_rubrique_modele').show();
        }
        else
        {
          $('#span_releve_appreciation_rubrique_modele').hide();
        }
      }
    );

    $('#form_contenu').on(
      'click' ,
      '#f_releve_appreciation_generale_report' ,
      function()
      {
        if($('#f_releve_appreciation_generale_report').is(':checked'))
        {
          $('#span_releve_appreciation_generale_modele').show();
        }
        else
        {
          $('#span_releve_appreciation_generale_modele').hide();
        }
      }
    );

    // bulletin report

    $('#form_contenu').on(
      'change' ,
      '#f_bulletin_appreciation_rubrique_longueur' ,
      function()
      {
        if(parseInt($('#f_bulletin_appreciation_rubrique_longueur').val(),10)>0)
        {
          $('#span_bulletin_appreciation_rubrique_report').show();
        }
        else
        {
          $('#span_bulletin_appreciation_rubrique_report').hide();
        }
      }
    );

    $('#form_contenu').on(
      'change' ,
      '#f_bulletin_appreciation_generale_longueur' ,
      function()
      {
        if(parseInt($('#f_bulletin_appreciation_generale_longueur').val(),10)>0)
        {
          $('#span_bulletin_appreciation_generale_report').show();
        }
        else
        {
          $('#span_bulletin_appreciation_generale_report').hide();
        }
      }
    );

    // bulletin modèle

    $('#form_contenu').on(
      'click' ,
      '#f_bulletin_appreciation_rubrique_report' ,
      function()
      {
        if($('#f_bulletin_appreciation_rubrique_report').is(':checked'))
        {
          $('#span_bulletin_appreciation_rubrique_modele').show();
        }
        else
        {
          $('#span_bulletin_appreciation_rubrique_modele').hide();
        }
      }
    );

    $('#form_contenu').on(
      'click' ,
      '#f_bulletin_appreciation_generale_report' ,
      function()
      {
        if($('#f_bulletin_appreciation_generale_report').is(':checked'))
        {
          $('#span_bulletin_appreciation_generale_modele').show();
        }
        else
        {
          $('#span_bulletin_appreciation_generale_modele').hide();
        }
      }
    );

    // relevé 

    $('#form_contenu').on(
      'click' ,
      '#f_releve_cases_auto' ,
      function()
      {
        $("#span_releve_cases_auto").toggle();
        $("#span_releve_cases_manuel").toggle();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerter sur la nécessité de valider
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_contenu").on(
      'change' ,
      '#input , #select , #textarea' ,
      function()
      {
        $('#ajax_msg_gestion').attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour choisir les matières (mise en place du formulaire)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_contenu').on(
      'click' ,
      '#span_moyennes q.choisir_compet' ,
      function()
      {
        cocher_matieres( $('#f_matiere_liste').val() );
        // Afficher la zone
        $.fancybox( { 'href':'#zone_matieres' , onStart:function(){$('#zone_matieres').css("display","block");} , onClosed:function(){$('#zone_matieres').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
        $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des matières sans moyennes
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_matieres').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $("#zone_matieres input[type=checkbox]:checked").each
        (
          function()
          {
            liste += $(this).val()+'_';
            nombre++;
          }
        );
        liste  = (nombre==0) ? '' : liste.substring(0,liste.length-1) ;
        nombre = (nombre==0) ? 'Sans exception (toutes matières avec moyennes)' : ( (nombre==1) ? 'Une exception (matière sans moyenne)' : ' '+nombre+' exceptions (matières sans moyennes)' ) ;
        $('#f_matiere_liste').val(liste);
        $('#f_matiere_nombre').val(nombre);
        $('#ajax_msg_bulletin').attr('class','alerte').html("Enregistrer pour confirmer.");
        $('#annuler_matieres').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour annuler le choix des matières
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_matieres').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enregistrement d'une modification d'affectation d'une configuration à une classe
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_affectation').on(
      'change' ,
      'select' ,
      function()
      {
        // Récupérer les informations
        type       = $(this).attr('name');
        reference  = $(this).find('option:selected').val();
        var classe = $(this).parent().parent().attr('id').substring(3);
        // Appel ajax transparent
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action='+'affecter'+'&f_type='+type+'&f_reference='+reference+'&f_classe='+classe,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
              }
            }
          }
        );
      }
    );

  }
);
