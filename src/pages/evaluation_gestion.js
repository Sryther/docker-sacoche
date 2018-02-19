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

    var mode                 = false;
    var modification         = false;
    var memo_pilotage        = 'clavier';
    var memo_direction       = 'down';
    var memo_input_id        = false;
    var colonne              = 1;
    var ligne                = 1;
    var nb_colonnes          = 1;
    var nb_lignes            = 1;
    var nb_lignes_max        = 20;
    var nb_caracteres_max    = 2000;
    var audio_duree_restante = 0;
    var $table_saisir_voir   = $('#table_saisir_voir');
    var item_reste           = { "liste" : '' , "nombre" : 0 };

    // tri du tableau (avec jquery.tablesorter.js).
    if(TYPE=='groupe')
    {
      var sorting = [[0,1],[3,0]];
      $('#table_action').tablesorter({ headers:{0:{sorter:'date_fr'},1:{sorter:false},2:{sorter:false},4:{sorter:false},6:{sorter:false},7:{sorter:false},8:{sorter:false},9:{sorter:false}} });
    }
    else
    {
      var sorting = [[0,1],[5,0]];
      $('#table_action').tablesorter({ headers:{0:{sorter:'date_fr'},1:{sorter:false},2:{sorter:false},3:{sorter:false},4:{sorter:false},6:{sorter:false},7:{sorter:false},8:{sorter:false},9:{sorter:false}} });
    }
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ sorting ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonctions utilisées
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function activer_boutons_upload(ref)
    {
      $('#zone_upload button').prop('disabled',false);
      if(!tab_sujets[ref])     {$('#bouton_supprimer_sujet').prop('disabled',true);}
      if(!tab_corriges[ref]) {$('#bouton_supprimer_corrige').prop('disabled',true);}
    }

    function maj_choix_tri_eleves()
    {
      groupe_id = $("#f_groupe option:selected").val();
      if(groupe_id)
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label');
        if(groupe_type=='Classes')
        {
          $("#bloc_ordre").hide();
        }
        else
        {
          $("#bloc_ordre").show();
        }
      }
      else
      {
        $("#bloc_ordre").hide();
      }
    }

    function afficher_form_gestion( mode , ref , date_fr , date_visible , date_autoeval , groupe_val , groupe_nom , eleve_nombre , eleve_liste , eleves_ordre , prof_nombre , prof_liste , description , compet_nombre , compet_liste , doc_sujet , doc_corrige , fini , proprio_id )
    {
      // Éviter, en cas de duplication d'évaluation dont on n'est pas le propriétaire, de se retrouver avec des complications
      // (droit du propriétaire d'origine ? évaluations en exemplaires multiples pour les autres ?)
      if( (mode=='dupliquer') && (user_id!=proprio_id) )
      {
        prof_nombre = 'non';
        prof_liste = '';
      }
      // Choix des collègues à masquer en cas de modification d'une évaluation dont on n'est pas le propriétaire
      // (ingérable sinon : on apparait comme propriétaire, le vrai propriétaire n'apparait pas comme tel...)
      if( (mode=='modifier') && (user_id!=proprio_id) )
      {
        $('#choisir_prof').hide(0);
        $('#choisir_prof_non').show(0);
      }
      else
      {
        $('#choisir_prof').show(0);
        $('#choisir_prof_non').hide(0);
      }
      $('#f_action').val(mode);
      $('#f_ref').val(ref);
      $('#f_date').val(date_fr);
      if(TYPE=='groupe')
      {
        var selected_groupe = (mode=='ajouter') ? select_groupe.replace('value="'+groupe_val+'"','value="'+groupe_val+'" selected') : select_groupe.replace('>'+groupe_nom,' selected>'+groupe_nom) ;
        $('#f_groupe').html(selected_groupe);
        maj_choix_tri_eleves();
      }
      else
      {
        $('#f_eleve_nombre').val(eleve_nombre);
        $('#f_eleve_liste').val(eleve_liste);
      }
      if(eleves_ordre)
      {
        $('#f_eleves_ordre option[value='+eleves_ordre+']').prop('selected',true);
      }
      $('#f_prof_nombre').val(prof_nombre);
      $('#f_prof_liste').val(prof_liste);
      $('#f_description').val(description);
      $('#f_compet_nombre').val(compet_nombre);
      $('#f_compet_liste').val(compet_liste);
      $('#f_doc_sujet').val(doc_sujet);
      $('#f_doc_corrige').val(doc_corrige);
      $('#f_fini').val(fini);
      // date de visibilité
      if(date_visible=='identique')
      { 
        $('#box_visible').prop('checked',true).next().show(0);
        $('#f_date_visible').parent().hide(0);
      }
      else
      {
        $('#box_visible').prop('checked',false).next().hide(0);
        $('#f_date_visible').val(date_visible).parent().show(0);
      }
      // date fin auto-évaluation
      if(date_autoeval=='sans objet')
      {
        $('#box_autoeval').prop('checked',true).next().show(0);
        $('#f_date_autoeval').val('00/00/0000').parent().hide(0);
      }
      else
      {
        $('#box_autoeval').prop('checked',false).next().hide(0);
        $('#f_date_autoeval').val(date_autoeval).parent().show(0);
      }
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " une évaluation");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(description);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#alerte_groupe').hide(0);
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':700 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_description').focus(); }
    }

    /**
     * Ajouter une évaluation : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Report des valeurs transmises via un formulaire depuis un tableau de synthèse bilan (1er affichage seulement)
      if(reception_todo)
      {
        reception_todo = false;
      }
      else
      {
        reception_users_texte = 'aucun';
        reception_items_texte = 'aucun';
        reception_users_liste = '';
        reception_items_liste = '';
      }
      var groupe_val = (TYPE=='groupe') ? $('#f_aff_classe option:selected').val() : '' ;
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*ref*/ , '' /*date_fr*/ , 'identique' /*date_visible*/ , 'sans objet' /*date_autoeval*/ , groupe_val , '' /*groupe_nom*/ , reception_users_texte /*eleve_nombre*/ , reception_users_liste /*eleve_liste*/ , '' /*eleves_ordre*/ , 'non' /*prof_nombre*/ , '' /*prof_liste*/ , '' /*description*/ , reception_items_texte /*compet_nombre*/ , reception_items_liste /*compet_liste*/ , '' /*doc_sujet*/ , '' /*doc_corrige*/ , '' /*fini*/ , user_id /*proprio_id*/ );
    };

    /**
     * Modifier | Dupliquer une évaluation : mise en place du formulaire
     * @return void
     */
    var modifier_dupliquer = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var date_visible  = objet_tds.eq(1).html();
      var date_autoeval = objet_tds.eq(2).html();
      if(TYPE=='groupe')
      {
        var groupe_nom    = objet_tds.eq(3).html();
        var eleve_nombre  = '';
        var eleve_liste   = '';
      }
      else
      {
        var groupe_nom    = '';
        var eleve_nombre  = objet_tds.eq(3).text().trim();
        var eleve_liste   = tab_eleves[ref];
      }
      var eleves_ordre  = objet_tds.eq(3).attr('class');
      var prof_nombre   = objet_tds.eq(4).text().trim();
      var proprio_id    = objet_tds.eq(4).attr('id').substring(8); // "proprio_" + ref
      var description   = objet_tds.eq(5).html();
      var compet_nombre = objet_tds.eq(6).html();
      var fini          =(objet_tds.eq(8).find('span').text()=='terminé') ? 'oui' : 'non' ;
      // liste des profs et des items
      var prof_liste    = tab_profs[ref];
      var compet_liste  = tab_items[ref];
      // Afficher le formulaire
      afficher_form_gestion( mode , ref , date_fr , date_visible , date_autoeval , '' /*groupe_val*/ , groupe_nom /* volontairement sans unescapeHtml() */ , eleve_nombre , eleve_liste , eleves_ordre , prof_nombre , prof_liste , unescapeHtml(description) , compet_nombre , compet_liste , tab_sujets[ref] , tab_corriges[ref] , fini , proprio_id );
    };

    /**
     * Supprimer une évaluation : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var groupe_nom    = objet_tds.eq(3).text().trim();
      var description   = objet_tds.eq(5).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , ref , '' /*date_fr*/ , '' /*date_visible*/ , '' /*date_autoeval*/ , '' /*groupe_val*/ , '' /*groupe_nom*/ , '' /*eleve_nombre*/ , '' /*eleve_liste*/ , '' /*eleves_ordre*/ , '' /*prof_nombre*/ , '' /*prof_liste*/ , unescapeHtml(description+' ('+groupe_nom+')') , '' /*compet_nombre*/ , '' /*compet_liste*/ , '' /*doc_sujet*/ , '' /*doc_corrige*/ , '' /*fini*/ , user_id /*proprio_id*/ );
    };

    /**
     * Imprimer un cartouche d'une évaluation : mise en place du formulaire
     * @return void
     */
    var imprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).text().trim();
      var eleves_ordre  = objet_tds.eq(3).attr('class');
      var description   = objet_tds.eq(5).html();
      // Mettre les infos de côté
      $('#imprimer_ref').val(ref);
      $('#imprimer_date_fr').val(date_fr);
      $('#imprimer_groupe_nom').val(unescapeHtml(groupe));
      $('#imprimer_eleves_ordre').val(eleves_ordre);
      $('#imprimer_description').val(unescapeHtml(description));
      // Afficher la zone associée
      $('#titre_imprimer').html(groupe+' | '+date_fr+' | '+description);
      $('#ajax_msg_imprimer').removeAttr('class').html("");
      $('#zone_imprimer_retour').html("");
      $.fancybox( { 'href':'#zone_imprimer' , onStart:function(){$('#zone_imprimer').css("display","block");} , onClosed:function(){$('#zone_imprimer').css("display","none");} , 'modal':true , 'minWidth':800 , 'centerOnScroll':true } );
    };

    /**
     * Annuler une action
     * @return void
     */
    var annuler = function()
    {
      $.fancybox.close();
      mode = false;
    };

    /**
     * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
     * @return void
     */
    function intercepter(e)
    {
      if( (mode=='ajouter') || (mode=='dupliquer') || (mode=='modifier') || (mode=='supprimer') )
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

    /**
     * Saisir les items acquis par les élèves à une évaluation : chargement du formulaire
     * Voir   les items acquis par les élèves à une évaluation : chargement des données
     * @return void
     */
    var saisir_ou_voir = function()
    {
      // Afficher au chargement
      if(auto_voir_devoir_id && auto_voir_groupe_type && auto_voir_groupe_id)
      {
        mode = 'voir';
        var objet_tds = $('#devoir_'+auto_voir_devoir_id+'_'+auto_voir_groupe_type+auto_voir_groupe_id).parent().find('td');
        auto_voir_devoir_id = false;
        auto_voir_groupe_id = false;
      }
      else
      {
        mode = $(this).attr('class');
        var objet_tds     = $(this).parent().parent().find('td');
      }
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var date_visible  = objet_tds.eq(1).html();
      var groupe        = objet_tds.eq(3).text().trim();
      var eleves_ordre  = objet_tds.eq(3).attr('class');
      var description   = objet_tds.eq(5).html();
      var fini          =(objet_tds.eq(8).find('span').text()=='terminé') ? 'oui' : 'non' ;
      // Mettre les infos de côté
      $('#saisir_voir_ref'         ).val(ref);
      $('#saisir_voir_date_fr'     ).val(date_fr);
      $('#saisir_voir_date_visible').val(date_visible);
      $('#saisir_voir_groupe_nom'  ).val(unescapeHtml(groupe));
      $('#saisir_voir_eleves_ordre').val(eleves_ordre);
      $('#saisir_voir_description' ).val(unescapeHtml(description));
      $('#saisir_voir_fini'        ).val(fini);
      // pour finir
      $('#zone_saisir_voir h2').html(mode[0].toUpperCase() + mode.substring(1) + " les résultats d'une évaluation");
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref+'&f_eleves_ordre='+eleves_ordre+'&f_description='+encodeURIComponent(description)+'&f_groupe_nom='+encodeURIComponent(groupe)+'&f_date_fr='+encodeURIComponent(date_fr),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            return false;
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
              modification = false;
              $.fancybox.close();
              // Masquer le tableau ; Afficher la zone associée et remplir son contenu
              $('#form_prechoix , #table_action').hide('fast');
              $('#titre_saisir_voir').html(groupe+' | '+date_fr+' | '+description);
              $('#ajax_msg_saisir_voir').removeAttr('class').html('&nbsp;');
              $table_saisir_voir.html(responseJSON['value']);
              $table_saisir_voir.find("img[data-mode=bulle]").css('display','none'); // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
              if(mode=='saisir')
              {
                $('#valider_saisir').show();
                $('#para_report_note').show();
                colorer_cellules();
                $('#radio_'+memo_pilotage).click();
                $('#arrow_continue_'+memo_direction).click();
              }
              else if(mode=='voir')
              {
                $('#valider_saisir').hide();
                $('#para_report_note').hide();
                $table_saisir_voir.find('tbody td').css({"background-color":"#DDF","text-align":"center","vertical-align":"middle","font-size":"110%"});
              }
              nb_colonnes = $table_saisir_voir.find('thead th').length;
              nb_lignes   = $table_saisir_voir.find('tbody tr').length;
              $('#zone_saisir_voir').show();
              if(nb_lignes>nb_lignes_max)
              {
                $table_saisir_voir.stickyTableHeaders();
              }
              if(mode=='saisir')
              {
                if(memo_pilotage=='clavier')
                {
                  $('#C'+colonne+'L'+ligne).focus();
                  if(isMobile)
                  {
                    $('#cadre_tactile').show();
                  }
                }
                else
                {
                  $('#arrow_continue').hide();
                }
              }
            }
          }
        }
      );
    };

    /**
     * Choisir les items associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var choisir_compet = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      $('#f_selection_items option:first').prop('selected',true);
      item_reste = cocher_matieres_items( $('#f_compet_liste').val() );
      if(mode=='modifier') {$('#alerte_items').show();}
      else                 {$('#alerte_items').hide();}
      // En cas d'évaluation partagée où tous les profs ne sont pas tous reliés aux référentiels concernés
      if(item_reste.nombre) {$('#info_items').show();}
      else                  {$('#info_items').hide();}
      // Afficher la zone
      $.fancybox( { 'href':'#zone_matieres_items' , onStart:function(){$('#zone_matieres_items').css("display","block");} , onClosed:function(){$('#zone_matieres_items').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Choisir les élèves associés à une évaluation : mise en place du formulaire (uniquement pour des élèves sélectionnés)
     * @return void
     */
    var choisir_eleve = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      $('#zone_eleve q.date_calendrier').show();
      $('#zone_eleve li.li_m1 span.gradient_pourcent').html('');
      $('#msg_indiquer_eleves_deja').removeAttr('class').html('');
      cocher_eleves( $('#f_eleve_liste').val() );
      if(mode=='modifier') {$('#alerte_eleves').show();}
      else                 {$('#alerte_eleves').hide();}
      // Afficher la zone
      $.fancybox( { 'href':'#zone_eleve' , onStart:function(){$('#zone_eleve').css("display","block");} , onClosed:function(){$('#zone_eleve').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Réordonner les items associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var ordonner = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).text().trim();
      var description   = objet_tds.eq(5).html();
      // Mettre les infos de côté
      $('#ordre_ref').val(ref);
      // Afficher la zone associée après avoir chargé son contenu
      $('#titre_ordonner').html(groupe+' | '+date_fr+' | '+description);
      $('#ajax_msg_ordonner').removeAttr('class').html('&nbsp;');
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            return false;
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
              modification = false;
              $('#sortable_v').html(responseJSON['value']).sortable( { cursor:'ns-resize' , update:function(event,ui){modif_ordre();} } );
              // Afficher la zone
              $.fancybox( { 'href':'#zone_ordonner' , onStart:function(){$('#zone_ordonner').css("display","block");} , onClosed:function(){$('#zone_ordonner').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
            }
          }
        }
      );
    };
    function modif_ordre()
    {
      if(modification==false)
      {
        $('#fermer_zone_ordonner').attr('class',"annuler").html('Annuler / Retour');
        modification = true;
        $('#ajax_msg_ordonner').removeAttr('class').html("");
      }
    }

    /**
     * Voir les répartitions des élèves à une évaluation : chargement des données
     * @return void
     */
    function ajax_statistiques()
    {
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+mode+'&'+$("#zone_voir_repart").serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            return false;
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
              // Afficher la zone
              $('#table_voir_repart_quantitative').html(responseJSON['quantitative']);
              $('#table_voir_repart_quantitative tbody td').css({"background-color":"#DDF","font-weight":"normal","text-align":"center"});
              $('#table_voir_repart_nominative').html(responseJSON['nominative']);
              $('#table_voir_repart_nominative tbody td').css({"background-color":"#DDF","font-weight":"normal","font-size":"85%"});
              $('#lien_repart_nominative').attr('href',responseJSON['href']);
              $('#ajax_msg_archiver_repart').removeAttr('class').html("");
              $.fancybox( { 'href':'#zone_voir_repart' , onStart:function(){$('#zone_voir_repart').css("display","block");} , onClosed:function(){$('#zone_voir_repart').css("display","none");} , 'centerOnScroll':true } );
            }
          }
        }
      );
    };

    /**
     * Voir les répartitions des élèves à une évaluation : chargement des données
     * @return void
     */
    var voir_repart = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).text().trim();
      var description   = objet_tds.eq(5).html();
      // Et aussi...
      var categorie_autre = ($('#f_categorie_autre').is(':checked')) ? 1 : 0 ;
      // Mettre les infos de côté
      $('#repart_ref').val(ref);
      $('#repart_date_fr').val(date_fr);
      $('#repart_groupe_nom').val(unescapeHtml(groupe));
      $('#repart_description').val(unescapeHtml(description));
      // Afficher la zone associée après avoir chargé son contenu
      $('#titre_voir_repart').html(groupe+' | '+date_fr+' | '+description);
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      ajax_statistiques();
    };

    $('#f_categorie_autre , #f_ref_pourcentage').change
    (
      function()
      {
        ajax_statistiques();
      }
    );

    /**
     * Choisir les professeurs associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var choisir_prof = function()
    {
      selectionner_profs_option( $('#f_prof_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Uploader les documents associés à une évaluation : mise en place du formulaire
     * @return void
     */
    var uploader_doc = function()
    {
      mode = $(this).attr('class');
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).text().trim();
      var description   = objet_tds.eq(5).html();
      // Sujet & Corrigé
      var img_sujet     = (tab_sujets[ref])   ? '<a href="'+tab_sujets[ref]+'" target="_blank" rel="noopener noreferrer" class="no_puce"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Sujet disponible." /></a>' : '<img alt="sujet" src="./_img/document/sujet_non.png" />' ;
      var img_corrige   = (tab_corriges[ref]) ? '<a href="'+tab_corriges[ref]+'" target="_blank" rel="noopener noreferrer" class="no_puce"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Corrigé disponible." /></a>' : '<img alt="corrigé" src="./_img/document/corrige_non.png" />' ;
      // Renseigner les champs dynamique affichés
      $('#titre_upload').html(groupe+' | '+date_fr+' | '+description);
      $('#ajax_document_upload').removeAttr('class').html("");
      $('#span_sujet').html(img_sujet);
      $('#span_corrige').html(img_corrige);
      $('#uploader_ref').val(ref);
      activer_boutons_upload(ref);
      // Afficher la zone
      $.fancybox( { 'href':'#zone_upload' , onStart:function(){$('#zone_upload').css("display","block");} , onClosed:function(){$('#zone_upload').css("display","none");} , 'minWidth':800 , 'modal':true , 'centerOnScroll':true } );
    };

    /**
     * Générer des énoncés d'évaluation
     * @return void
     */
    var generer_enonces = function()
    {
      var objet_tds     = $(this).parent().parent().find('td');
      // Récupérer les informations de la ligne concernée
      var ref           = objet_tds.eq(9).attr('id').substring(7); // "devoir_" + ref
      var date_fr       = objet_tds.eq(0).html();
      var groupe        = objet_tds.eq(3).text().trim();
      var eleves_ordre  = objet_tds.eq(3).attr('class');
      var description   = objet_tds.eq(5).html();
      $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action='+'generer_enonces'+'&f_ref='+ref+'&f_eleves_ordre='+eleves_ordre+'&f_description='+encodeURIComponent(description)+'&f_groupe_nom='+encodeURIComponent(groupe)+'&f_date_fr='+encodeURIComponent(date_fr),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
            return false;
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
              $.fancybox( { 'href':responseJSON['value'] , 'type':'iframe' } );
            }
          }
        }
      );
    };

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel des fonctions en fonction des événements
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'        , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'       , modifier_dupliquer );
    $('#table_action').on( 'click' , 'q.dupliquer'      , modifier_dupliquer );
    $('#table_action').on( 'click' , 'q.supprimer'      , supprimer );
    $('#table_action').on( 'click' , 'q.ordonner'       , ordonner );
    $('#table_action').on( 'click' , 'q.imprimer'       , imprimer );
    $('#table_action').on( 'click' , 'q.saisir'         , saisir_ou_voir );
    $('#table_action').on( 'click' , 'q.voir'           , saisir_ou_voir );
    $('#table_action').on( 'click' , 'q.voir_repart'    , voir_repart );
    $('#table_action').on( 'click' , 'q.uploader_doc'   , uploader_doc );
    $('#table_action').on( 'click' , 'q.module_envoyer' , generer_enonces );

    $('#form_gestion').on( 'click' , 'q.choisir_compet' , choisir_compet );
    $('#form_gestion').on( 'click' , 'q.choisir_eleve'  , choisir_eleve );
    $('#form_gestion').on( 'click' , 'q.choisir_prof'   , choisir_prof );
    $('#form_gestion').on( 'click' , '#bouton_annuler'  , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider'  , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'     , function(e){intercepter(e);} );

    $('#zone_upload'  ).on( 'click' , '#fermer_zone_upload'   , annuler );
    $('#zone_ordonner').on( 'click' , '#fermer_zone_ordonner' , annuler );
    $('#zone_imprimer').on( 'click' , '#fermer_zone_imprimer' , annuler );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer au survol une liste de profs associés à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'mouseover',
      'img.bulle_profs',
      function()
      {
        var obj_image  = $(this);
        var ref        = obj_image.parent().parent().children('td:last').attr('id').substring(7); // "devoir_" + ref
        var proprio_id = obj_image.parent().attr('id').substring(8); // "proprio_" + ref
        var prof_liste = tab_profs[ref];
        var tab_texte  = new Array();;
        if(prof_liste.length)
        {
          prof_liste += '_z'+proprio_id;
          var tab_val = prof_liste.split('_');
          for(i in tab_val)
          {
            var val_option = tab_val[i].substring(0,1);
            var id_prof    = tab_val[i].substring(1);
            var id_select  = 'p'+'_'+id_prof;
            if($('#'+id_select).length)
            {
              tab_texte[i] = $('#'+id_select).next().next().text();
            }
            else
            {
              tab_texte[i] = 'collègue n°'+id_prof+'... ?';
            }
          }
          tab_texte.sort();
        }
        obj_image.attr( 'title' , tab_texte.join('<br />') );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer au survol une liste d'élèves associés à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'mouseover',
      'img.bulle_eleves',
      function()
      {
        var obj_image   = $(this);
        var ref         = obj_image.parent().parent().children('td:last').attr('id').substring(7); // "devoir_" + ref
        var eleve_liste = tab_eleves[ref];
        var tab_texte   = new Array();;
        if(eleve_liste.length)
        {
          var tab_id = eleve_liste.split('_');
          for(i in tab_id)
          {
            var id_debut = 'id_'+tab_id[i]+'_';
            if($('input[id^='+id_debut+']').length)
            {
              tab_texte[i] = $('input[id^='+id_debut+']').first().next().text();
            }
            else
            {
              tab_texte[i] = 'élève n°'+tab_id[i]+' (ne vous est pas affecté)';
            }
          }
          tab_texte.sort();
        }
        obj_image.attr( 'title' , tab_texte.join('<br />') );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification du select par lot pour tous les profs
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('input[name=prof_check_all]').click
    (
      function()
      {
        var valeur = $(this).val();
        $('#zone_profs').find('select').find('option[value='+valeur+']').prop('selected',true);
        $('.prof_liste').find('span.select_img').attr('class','select_img droit_'+valeur);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Modification du select pour choisir un droit à un prof
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_profs').on
    (
      'change',
      'select',
      function()
      {
        var val_option = $(this).find('option:selected').val();
        $(this).next('span').attr('class','select_img droit_'+val_option);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le checkbox pour choisir ou non une date visible différente de la date du devoir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_visible').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $(this).next().show(0).next().hide(0);
        }
        else
        {
          $(this).next().hide(0).next().show(0).children('input').val(input_visible).focus();
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le checkbox pour choisir ou non une date d'auto-évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_autoeval').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $(this).next().show(0).next().hide(0).children('input').val('00/00/0000');
        }
        else
        {
          $(this).next().hide(0).next().show(0).children('input').focus().val(input_autoeval);
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerte si modification de groupe d'une évaluation
    // Afficher / Masquer le mode de tri des élèves
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_groupe').change
    (
      function()
      {
        // Alerte si modification de groupe d'une évaluation
        if(mode=='modifier')
        {
          $('#alerte_groupe').show();
        }
        // Afficher / Masquer le mode de tri des élèves
        maj_choix_tri_eleves();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le cadre des items associés à une évaluation (annuler / retour)
    // Clic sur le bouton pour fermer le cadre des élèves associés à une évaluation (annuler / retour) (uniquement pour des élèves sélectionnés)
    // Clic sur le bouton pour fermer le cadre des professeurs associés à une évaluation (annuler / retour)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_compet , #annuler_eleve , #annuler_profs').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':700 , 'centerOnScroll':true } );
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le formulaire servant à saisir ou voir les résultats des élèves à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function fermer_zone_saisir_voir()
    {
      $('#titre_saisir_voir').html("");
      $table_saisir_voir.html("<tbody><tr><td></td></tr></tbody>");
      if(nb_lignes>nb_lignes_max)
      {
        $table_saisir_voir.stickyTableHeaders('destroy');
      }
      if( (mode=='saisir') && isMobile )
      {
        $('#cadre_tactile').hide();
      }
      $('#zone_saisir_voir').hide();
      $('#form_prechoix , #table_action').show('fast');
      return false;
    }

    $('#fermer_zone_saisir_voir').click
    (
      function()
      {
        if( (mode=='voir') || !modification )
        {
          fermer_zone_saisir_voir();
        }
        else
        {
          $.fancybox( { 'href':'#zone_confirmer_fermer_saisir' , onStart:function(){$('#zone_confirmer_fermer_saisir').css("display","block");} , onClosed:function(){$('#zone_confirmer_fermer_saisir').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
          return false;
        }
      }
    );

    $('#confirmer_fermer_zone_saisir').click
    (
      function()
      {
        $.fancybox.close();
        fermer_zone_saisir_voir();
      }
    );

    $('#annuler_fermer_zone_saisir').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des items associés à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_compet').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $("#zone_matieres_items input[type=checkbox]:checked").each
        (
          function()
          {
            liste += $(this).val()+'_';
            nombre++;
          }
        );
        // En cas d'évaluation partagée où tous les profs ne sont pas tous reliés aux référentiels concernés
        if(item_reste.nombre)
        {
          liste += item_reste.liste+'_';
          nombre += item_reste.nombre;
        }
        var compet_liste  = liste.substring(0,liste.length-1);
        var compet_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' items' : nombre+' item' ) ;
        $('#f_compet_liste').val(compet_liste);
        $('#f_compet_nombre').val(compet_nombre);
        $('#annuler_compet').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des élèves associés à une évaluation (uniquement pour des élèves sélectionnés)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_eleve').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        var test_doublon = new Array();
        $("#zone_eleve input[type=checkbox]:checked").each
        (
          function()
          {
            var eleve_id = $(this).val();
            if(typeof(test_doublon[eleve_id])=='undefined')
            {
              test_doublon[eleve_id] = true;
              liste += eleve_id+'_';
              nombre++;
            }
          }
        );
        var eleve_liste  = liste.substring(0,liste.length-1);
        var eleve_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' élèves' : nombre+' élève' ) ;
        $('#f_eleve_liste').val(eleve_liste);
        $('#f_eleve_nombre').val(eleve_nombre);
        $('#annuler_eleve').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des profs associés à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_profs').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $('#zone_profs').find('select').each
        (
          function()
          {
            var val_option = $(this).find('option:selected').val();
            if( (val_option!='x') && (val_option!='z') )
            {
              var tab_val = $(this).attr('id').split('_');
              var id_prof = tab_val[1];
              liste += val_option+id_prof+'_';
              nombre++;
            }
          }
        );
        liste  = (!nombre) ? '' : liste.substring(0,liste.length-1) ;
        nombre = (!nombre) ? 'non' : (nombre+1)+' profs' ;
        $('#f_prof_liste').val(liste);
        $('#f_prof_nombre').val(nombre);
        $('#annuler_profs').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Demande pour sélectionner d'une liste d'items mémorisés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_selection_items').change
    (
      function()
      {
        item_reste = cocher_matieres_items( $("#f_selection_items").val() );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour mémoriser un choix d'items
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_enregistrer_items').click
    (
      function()
      {
        memoriser_selection_matieres_items( $("#f_liste_items_nom").val() );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonction pour colorer les cases du tableau de saisie des items déjà enregistrés
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function colorer_cellules()
    {
      $table_saisir_voir.find("tbody td input").each
      (
        function ()
        {
          if( ($(this).val()!='X') && ($(this).val()!='PA') )
          {
            $(this).parent().css("background-color","#AAF");
          }
          else
          {
            $(this).parent().css("background-color","#EEF");
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Validation de la demande de génération d'un cartouche pour une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_imprimer').click
    (
      function()
      {
        $('#zone_imprimer button').prop('disabled',true);
        $('#ajax_msg_imprimer').attr('class','loader').html("En cours&hellip;");
        $('#zone_imprimer_retour').html("");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=imprimer_cartouche'+'&'+$("#zone_imprimer").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_imprimer button').prop('disabled',false);
              $('#ajax_msg_imprimer').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#zone_imprimer button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_imprimer').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_imprimer').attr('class','valide').html("Cartouches générés !");
                $('#zone_imprimer_retour').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Validation de la demande de génération d'un PDF de répartition des scores
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#archiver_repart').click
    (
      function()
      {
        $('#archiver_repart').prop('disabled',true);
        $('#ajax_msg_archiver_repart').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=archiver_repart'+'&'+$("#zone_voir_repart").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#archiver_repart').prop('disabled',false);
              $('#ajax_msg_archiver_repart').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#archiver_repart').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_archiver_repart').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_archiver_repart').removeAttr('class').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour afficher la zone "Saisie déportée & Archivage"
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#afficher_deport_archivage').click
    (
      function()
      {
        $('#ajax_msg_deport_archivage').removeAttr('class').html('');
        if(mode=='saisir')
        {
          $('#zone_deport_archivage').find('li.saisir').show();
          $('#zone_deport_archivage').find('li.voir').hide();
        }
        else if(mode=='voir')
        {
          $('#zone_deport_archivage').find('li.voir').show();
          $('#zone_deport_archivage').find('li.saisir').hide();
        }
        $.fancybox( { 'href':'#zone_deport_archivage' , onStart:function(){$('#zone_deport_archivage').css("display","block");} , onClosed:function(){$('#zone_deport_archivage').css("display","none");} , 'minHeight':300 , 'minWidth':600 , 'centerOnScroll':true } );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur un bouton pour générer un CSV ou un PDF depuis la zone "Saisie déportée & Archivage"
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_deport_archivage').on
    (
      'click',
      'button[id^=generer_tableau_scores]',
      function()
      {
        $('#f_archivage_action').val( $(this).attr('id') );
        $('#zone_deport_archivage button').prop('disabled',true);
        $('#ajax_msg_deport_archivage').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&'+$("#zone_deport_archivage").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_deport_archivage button').prop('disabled',false);
              $('#ajax_msg_deport_archivage').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#zone_deport_archivage button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_deport_archivage').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_deport_archivage').removeAttr('class').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Demande d'indiquer la liste des élèves associés à une évaluation de même nom (uniquement pour des élèves sélectionnés)
    // Reprise d'un développement initié par Alain Pottier <alain.pottier613@orange.fr>
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#indiquer_eleves_deja').click
    (
      function()
      {
        if(!$('#f_description').val())
        {
          $('#msg_indiquer_eleves_deja').attr('class','erreur').html('évaluation sans nom');
          return false;
        }
        var f_date_debut = $('#f_date_deja').val();
        if(!f_date_debut)
        {
          $('#msg_indiquer_eleves_deja').attr('class','erreur').html('date manquante');
          return false;
        }
        if(!test_dateITA(f_date_debut))
        {
          $('#msg_indiquer_eleves_deja').attr('class','erreur').html('date JJ/MM/AAAA incorrecte');
          return false;
        }
        $('button').prop('disabled',true);
        $('#msg_indiquer_eleves_deja').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=indiquer_eleves_deja'+'&f_description='+encodeURIComponent($('#f_description').val())+'&f_date_debut='+encodeURIComponent(f_date_debut),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#msg_indiquer_eleves_deja').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#msg_indiquer_eleves_deja').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                // On récupère les associations élèves -> dates
                var tab_dates   = new Array();
                var tab_groupes = new Array();
                var memo_groupe_id = 0;
                for(i in responseJSON)
                {
                  if( i != 'statut' )
                  {
                    var tab = responseJSON[i].split('_');
                    tab_dates[tab[0]] = tab[1];
                  }
                }
                // Passer en revue les lignes élève
                $("#zone_eleve input[type=checkbox]").each
                (
                  function()
                  {
                    var tab_ids = $(this).attr('id').split('_');
                    var eleve_id  = tab_ids[1];
                    var groupe_id = tab_ids[2];
                    var eleve_date = tab_dates[eleve_id];
                    if(groupe_id!=memo_groupe_id)
                    {
                      memo_groupe_id = groupe_id;
                      tab_groupes[groupe_id] = new Array(0,0);
                    }
                    $(this).next('label').removeAttr('class').next('span').html('');
                    if(typeof(eleve_date)=='undefined')
                    {
                      tab_groupes[groupe_id][0]++;
                    }
                    else
                    {
                      $(this).next('label').addClass('deja grey').next('span').html('<span>'+eleve_date+'</span>');
                      tab_groupes[groupe_id][1]++;
                    }
                  }
                );
                // Passer en revue les bilans par groupe
                for(groupe_id in tab_groupes)
                {
                  var nb_eleves = tab_groupes[groupe_id][0]+tab_groupes[groupe_id][1];
                  var pourcentage = (nb_eleves) ? (100*tab_groupes[groupe_id][1]/nb_eleves).toFixed(0) : 0 ;
                  switch (pourcentage)
                  {
                    case '0'   : var couleur = '#C00';break;
                    case '100' : var couleur = '#080';break;
                    default    : var couleur = '#333';break;
                  }
                  $('#groupe_'+groupe_id).css('color',couleur).html('<span class="gradient_outer"><span class="gradient_inner" style="width:'+pourcentage+'px"></span></span>'+pourcentage+'%');
                }
                $('#msg_indiquer_eleves_deja').attr('class','valide').html("Affichage actualisé.");
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix du mode de pilotage pour la saisie des résultats
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $table_saisir_voir.on
    (
      'click',
      'input[name=mode_saisie]',
      function()
      {
        memo_pilotage = $(this).val();
        if(memo_pilotage=='clavier')
        {
          $('#arrow_continue').show(0);
          $('#C'+colonne+'L'+ligne).focus();
          if(isMobile)
          {
            $('#cadre_tactile').show();
          }
        }
        else
        {
          $('#arrow_continue').hide(0);
          if(isMobile)
          {
            $('#cadre_tactile').hide();
          }
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix du sens de parcours pour la saisie des résultats (si pilotage au clavier)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $table_saisir_voir.on
    (
      'click',
      'input[name=arrow_continue]',
      function()
      {
        memo_direction = $(this).val();
        $('#C'+colonne+'L'+ligne).focus();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix de rétrécir ou pas les colonnes sur #table_saisir_voir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $table_saisir_voir.on
    (
      'click',
      '#check_largeur',
      function()
      {
        var condense = ($(this).is(':checked')) ? 'v' : 'h' ; // 'h' ou 'v' pour horizontal (non condensé) ou vertical (condensé)
        $table_saisir_voir.find("thead tr th img").each
        (
          function ()
          {
            img_src_old = $(this).attr('src');
            img_src_new = (condense=='v') ? img_src_old.substring(0,img_src_old.length-3) : img_src_old+'&br' ;
            $(this).attr('src',img_src_new);
          }
        );
        if(mode=='saisir')
        {
          $table_saisir_voir.find('tbody').attr('class',condense);
        }
        if(mode=='voir')
        {
          $table_saisir_voir.find("tbody tr td img").each
          (
            function ()
            {
              img_src_old = $(this).attr('src');
              img_src_new = (condense=='v') ? img_src_old.replace('/h/','/v/').replace('/h_','/v_') : img_src_old.replace('/v/','/h/').replace('/v_','/h_') ;
              $(this).attr('src',img_src_new);
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix de rétrécir ou pas les lignes sur #table_saisir_voir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $table_saisir_voir.on
    (
      'click',
      '#check_hauteur',
      function()
      {
        if($(this).is(':checked'))
        {
          $table_saisir_voir.find("div[data-mode=complet]").css('display','none');       // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
          $table_saisir_voir.find("img[data-mode=bulle]").css('display','inline-block'); // .show(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
        }
        else
        {
          $table_saisir_voir.find("img[data-mode=bulle]").css('display','none');    // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
          $table_saisir_voir.find("div[data-mode=complet]").css('display','block'); // .show(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Gérer la saisie des résultats au clavier ou avec un dispositif tactile
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function focus_cellule_suivante_en_evitant_sortie_tableau()
    {
      if(colonne==0)
      {
        colonne = nb_colonnes;
        ligne = (ligne!=1) ? ligne-1 : nb_lignes ;
      }
      else if(colonne>nb_colonnes)
      {
        colonne = 1;
        ligne = (ligne!=nb_lignes) ? ligne+1 : 1 ;
      }
      else if(ligne==0)
      {
        ligne = nb_lignes;
        colonne = (colonne!=1) ? colonne-1 : nb_colonnes ;
        if(nb_lignes>nb_lignes_max)
        {
          window.scrollTo(0,10000); /* jQuery TH Float Plugin */
        }
      }
      else if(ligne>nb_lignes)
      {
        ligne = 1;
        colonne = (colonne!=nb_colonnes) ? colonne+1 : 1 ;
        if(nb_lignes>nb_lignes_max)
        {
          window.scrollTo(0,150); /* jQuery TH Float Plugin */
        }
      }
      var new_id = 'C'+colonne+'L'+ligne;
      $('#'+new_id).focus();
    }

    $('#cadre_tactile').on
    (
      'click',
      'span',
      function()
      {
        var code = parseInt( $(this).attr('id').substring(4) , 10 ); // "kbd_" + ref
        navigation_clavier(code);
      }
    );

    $table_saisir_voir.on
    (
      'click',
      'tbody td input',
      function(e)
      {
        var cellule_id  = $(this).attr("id");
        colonne = parseInt(cellule_id.substring(1,cellule_id.indexOf('L')),10);
        ligne   = parseInt(cellule_id.substring(cellule_id.indexOf('L')+1),10);
      }
    );

    $table_saisir_voir.on
    (
      'keydown',  // keydown au lieu de keyup permet de laisser appuyer sur la touche pour répéter une action
      'tbody td input',
      function(e)
      {
        if(memo_pilotage=='clavier')
        {
          var cellule_id  = $(this).attr("id");
          colonne = parseInt(cellule_id.substring(1,cellule_id.indexOf('L')),10);
          ligne   = parseInt(cellule_id.substring(cellule_id.indexOf('L')+1),10);
          navigation_clavier(e.which);
        }
      }
    );

    function navigation_clavier(touche_code)
    {
      var findme = '.'+touche_code+'.';
      var endroit_report_note = 'cellule';
      if( keycode_search.indexOf(findme) != -1 )
      {
        // Une touche d'item a été pressée
        switch (touche_code)
        {
          case   8: var note = 'X' ; var style = note; break; // backspace
          case  46: var note = 'X' ; var style = note; break; // suppr
          case  65: var note = 'AB'; var style = note; break; // A
          case  68: var note = 'DI'; var style = note; break; // D
          case  78: var note = 'NN'; var style = note; break; // N
          case  69: var note = 'NE'; var style = note; break; // E
          case  70: var note = 'NF'; var style = note; break; // F
          case  82: var note = 'NR'; var style = note; break; // R
          case  80: var note = 'PA'; var style = note; break; // P
          default : var note = tab_keycode_note[touche_code]; var style = 'N'+note; break
        }
        endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
        if( (typeof(endroit_report_note)=='undefined') || (endroit_report_note=='cellule') )
        {
          // pour une seule case
          var cellule_obj = $('#C'+colonne+'L'+ligne);
          cellule_obj.val(note).attr('class',style);
          cellule_obj.parent().css("background-color","#F6D");
          if(memo_direction=='down')
          {
            ligne++;
          }
          else
          {
            colonne++;
          }
        }
        else if(endroit_report_note=='tableau')
        {
          // pour toutes les cases vides du tableau
          $table_saisir_voir.find("tbody td input").each
          (
            function()
            {
              if($(this).val()=='X')
              {
                $(this).val(note).attr('class',style);
                $(this).parent().css("background-color","#F6D");
              }
            }
          );
        }
        else if(endroit_report_note=='colonne')
        {
          // pour toutes les cases vides d'une colonne
          $table_saisir_voir.find("tbody td input[id^=C"+colonne+"L]").each
          (
            function()
            {
              if($(this).val()=='X')
              {
                $(this).val(note).attr('class',style);
                $(this).parent().css("background-color","#F6D");
              }
            }
          );
          colonne++;
        }
        else if(endroit_report_note=='ligne')
        {
          // pour toutes les cases vides d'une ligne
          $table_saisir_voir.find("tbody td input[id$=L"+ligne+"]").each
          (
            function()
            {
              if($(this).val()=='X')
              {
                $(this).val(note).attr('class',style);
                $(this).parent().css("background-color","#F6D");
              }
            }
          );
          ligne++;
        }
        if(modification==false)
        {
          $('#fermer_zone_saisir_voir').attr('class',"annuler").html('Annuler / Retour');
          $('#kbd_27').attr('class',"img annuler");
          $(window).on('beforeunload', confirmOnLeave );
          modification = true;
        }
        $('#ajax_msg_saisir_voir').removeAttr('class').html("");
        focus_cellule_suivante_en_evitant_sortie_tableau();
        endroit_report_note = 'cellule';
      }
      else if('.37.38.39.40.'.indexOf(findme)!=-1)
      {
        // Une flèche a été pressée
        switch (touche_code)
        {
          case 37: colonne--; break; // flèche gauche
          case 38: ligne--;   break; // flèche haut
          case 39: colonne++; break; // flèche droit
          case 40: ligne++;   break; // flèche bas
        }
        focus_cellule_suivante_en_evitant_sortie_tableau();
      }
      else if(touche_code==13)  // touche entrée
      {
        // La touche entrée a été pressée
        $('#valider_saisir').click();
      }
      else if(touche_code==27)
      {
        // La touche escape a été pressée
        $('#fermer_zone_saisir_voir').click();
      }
      else if('.67.76.84.'.indexOf(findme)!=-1)
      {
        // Une touche de préparation de modification par lot a été pressée
        switch (touche_code)
        {
          case 67: endroit_report_note = 'colonne'; break; // C
          case 76: endroit_report_note = 'ligne';   break; // L
          case 84: endroit_report_note = 'tableau'; break; // T
        }
      }
      else if('.16.17.18.20.144.'.indexOf(findme)!=-1)
      {
        // Une touche Shift / Ctrl / Alt / CapsLock / VerrNum [*] a été pressée
        // [*] 144 est aussi un signal particulier envoyé par un clavier étendu en parallèle à chaque appui sur une touche du pavé numérique pour signaler qu'il est actif
        endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
      }
      $('#f_report_'+endroit_report_note).prop('checked',true);
      return false; // Evite notamment qu'IE fasse "page précédente" si on appuie sur backspace.
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Gérer la saisie des résultats à la souris
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Remplacer la cellule par les images de choix
    $table_saisir_voir.on
    (
      'mouseover',
      'tbody td.td_clavier',
      function()
      {
        if(memo_pilotage=='souris')
        {
          // Test si un précédent td n'a pas été remis en place (js a du mal à suivre le mouseleave sinon)
          if(memo_input_id)
          {
            $("td#td_"+memo_input_id).attr('class',"td_clavier").children("div").remove();
            $("input#"+memo_input_id).show();
            memo_input_id = false;
            if(nb_lignes>nb_lignes_max)
            {
              $table_saisir_voir.stickyTableHeaders('destroy');
            }
          }
          else
          {
            // Récupérer les infos associées
            memo_input_id = $(this).children("input").attr("id");
            colonne = parseInt(memo_input_id.substring(1,memo_input_id.indexOf('L')),10);
            ligne   = parseInt(memo_input_id.substring(memo_input_id.indexOf('L')+1),10);
            var valeur = $(this).children("input").val();
            $(this).children("input").hide();
            $(this).attr('class',"td_souris").append( $("#td_souris_container").html() ).find("img[alt="+valeur+"]").addClass("on");
            if(nb_lignes>nb_lignes_max)
            {
              $table_saisir_voir.stickyTableHeaders();
            }
          }
        }
      }
    );

    // Revenir à la cellule initiale ; mouseout ne fonctionne pas à cause des éléments contenus dans le div ; mouseleave est mieux, mais pb qd même avec les select du calendrier
    $table_saisir_voir.on
    (
      'mouseleave',
      'tbody td',
      function()
      {
        if(memo_pilotage=='souris')
        {
          if(memo_input_id)
          {
            $("td#td_"+memo_input_id).attr('class',"td_clavier").children("div").remove();
            $("input#"+memo_input_id).show();
            memo_input_id = false;
          }
        }
      }
    );

    // Renvoyer l'information dans la ou les cellule(s)
    $table_saisir_voir.on
    (
      'click',
      'div.td_souris img',
      function()
      {
        var note = $(this).attr("alt");
        var style = isNaN(note) ? note : 'N'+note ;
        endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
        if( (typeof(endroit_report_note)=='undefined') || (endroit_report_note=='cellule') )
        {
          // pour une seule case
          $("input#"+memo_input_id).val(note).attr('class',style);
          $(this).parent().children("img").removeAttr('class');
          $(this).addClass("on").parent().parent().css("background-color","#F6D");
        }
        else
        {
          if(endroit_report_note=='tableau')
          {
            // pour toutes les cases vides du tableau
            $table_saisir_voir.find("tbody td input").each
            (
              function()
              {
                if($(this).val()=='X')
                {
                  $(this).val(note).attr('class',style);
                  $(this).parent().css("background-color","#F6D");
                }
              }
            );
          }
          else if(endroit_report_note=='colonne')
          {
            // pour toutes les cases vides d'une colonne
            $table_saisir_voir.find("tbody td input[id^=C"+colonne+"L]").each
            (
              function()
              {
                if($(this).val()=='X')
                {
                  $(this).val(note).attr('class',style);
                  $(this).parent().css("background-color","#F6D");
                }
              }
            );
          }
          else if(endroit_report_note=='ligne')
          {
            // pour toutes les cases vides d'une ligne
            $table_saisir_voir.find("tbody td input[id$=L"+ligne+"]").each
            (
              function()
              {
                if($(this).val()=='X')
                {
                  $(this).val(note).attr('class',style);
                  $(this).parent().css("background-color","#F6D");
                }
              }
            );
          }
        }
        if(modification==false)
        {
          $('#fermer_zone_saisir_voir').attr('class',"annuler").html('Annuler / Retour');
          $('#kbd_27').attr('class',"img annuler");
          $(window).on('beforeunload', confirmOnLeave );
          modification = true;
        }
        $('#ajax_msg_saisir_voir').removeAttr('class').html("");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le lien pour mettre à jour l'ordre des items d'une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_ordre').click
    (
      function()
      {
        if(modification==false)
        {
          $('#ajax_msg_ordonner').attr('class','alerte').html("Aucune modification effectuée !");
        }
        else
        {
          // On récupère la liste des items dans l'ordre de la page
          var tab_id = new Array();
          $('#sortable_v').children('li').each
          (
            function()
            {
              var test_id = $(this).attr('id');
              if(typeof(test_id)!='undefined')
              {
                tab_id.push(test_id.substring(1));
              }
            }
          );
          $('#zone_ordonner button').prop('disabled',true);
          $('#ajax_msg_ordonner').attr('class','loader').html("En cours&hellip;");
          var ref = $('#ordre_ref').val();
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=enregistrer_ordre'+'&f_ref='+ref+'&f_prof_liste='+tab_profs[ref]+'&tab_id='+tab_id,
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#zone_ordonner button').prop('disabled',false);
                $('#ajax_msg_ordonner').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
                return false;
              },
              success : function(responseJSON)
              {
                initialiser_compteur();
                $('#zone_ordonner button').prop('disabled',false);
                if(responseJSON['statut']==false)
                {
                  $('#ajax_msg_ordonner').attr('class','alerte').html(responseJSON['value']);
                }
                else
                {
                  modification = false;
                  $('#devoir_'+ref).parent().addClass("new");
                  $('#ajax_msg_ordonner').attr('class','valide').html("Ordre enregistré !");
                  $('#fermer_zone_ordonner').attr('class',"retourner").html('Retour');
                }
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le lien pour mettre à jour les résultats des élèves à une évaluation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_saisir').click
    (
      function()
      {
        if(modification==false)
        {
          $('#ajax_msg_saisir_voir').attr('class','alerte').html("Aucune modification effectuée !");
        }
        else
        {
          $('button').prop('disabled',true);
          $('#ajax_msg_saisir_voir').attr('class','loader').html("En cours&hellip;");
          // Grouper les saisies dans une variable unique afin d'éviter tout problème avec une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque) ou "max input vars" généralement fixé à 1000.
          var f_notes = new Array();
          $table_saisir_voir.find("tbody input").each
          (
            function()
            {
              var ids  = $(this).attr('name');
              var note = $(this).val();
              if(note)
              {
                f_notes.push( ids + '_' + note );
              }
            }
          );
          var ref = $('#saisir_voir_ref').val();
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=enregistrer_saisie'+'&f_ref='+ref+'&f_date_fr='+$("#saisir_voir_date_fr").val()+'&f_date_visible='+$("#saisir_voir_date_visible").val()+'&f_eleves_ordre='+$("#saisir_voir_eleves_ordre").val()+'&f_fini='+$("#saisir_voir_fini").val()+'&f_prof_liste='+tab_profs[ref]+'&f_notes='+f_notes+'&f_description='+encodeURIComponent($("#saisir_voir_description").val()),
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('button').prop('disabled',false);
                $('#ajax_msg_saisir_voir').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
                return false;
              },
              success : function(responseJSON)
              {
                modification = false; // Mis ici pour le cas "aucune modification détectée"
                $(window).off( 'beforeunload', confirmOnLeave );
                initialiser_compteur();
                $('button').prop('disabled',false);
                if(responseJSON['statut']==false)
                {
                  $('#ajax_msg_saisir_voir').attr('class','alerte').html(responseJSON['value']);
                }
                else
                {
                  $('#devoir_'+ref).parent().addClass("new");
                  $('#ajax_msg_saisir_voir').attr('class','valide').html("Saisies enregistrées !");
                  $('#fermer_zone_saisir_voir').attr('class',"retourner").html('Retour');
                  $('#kbd_27').attr('class',"img retourner");
                  colorer_cellules();
                  $("#devoir_"+$("#saisir_voir_ref").val()).prev().replaceWith(responseJSON['value']);
                }
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_date          : { required:true , dateITA:true },
          f_date_visible  : { required:function(){return !$('#box_visible').is(':checked');} , dateITA:true },
          f_date_autoeval : { required:function(){return !$('#box_autoeval').is(':checked');} , dateITA:true },
          f_groupe        : { required:true },
          f_eleve_liste   : { required:true },
          f_eleves_ordre  : { required:true },
          f_description   : { required:true , maxlength:60 },
          f_prof_liste    : { required:false },
          f_compet_liste  : { required:true },
          f_mode_discret  : { required:false },
          f_doc_sujet     : { required:false , url:true },
          f_doc_corrige   : { required:false , url:true }
        },
        messages :
        {
          f_date          : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_visible  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_autoeval : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_groupe        : { required:"groupe manquant" },
          f_eleve_liste   : { required:"élève(s) manquant(s)" },
          f_eleves_ordre  : { required:"ordre manquant" },
          f_description   : { required:"nom manquant" , maxlength:"60 caractères maximum" },
          f_prof_liste    : { },
          f_compet_liste  : { required:"item(s) manquant(s)" },
          f_mode_discret  : { },
          f_doc_sujet     : { url:" URL sujet invalide" },
          f_doc_corrige   : { url:" URL corrigé invalide" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if( '.f_date.f_date_visible.f_date_autoeval.'.indexOf('.'+element.attr("id")+'.') !== -1 ) { element.next().after(error); }
          else {element.after(error);}
        }
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
      target : "#ajax_msg_gestion",
      beforeSerialize : action_form_avant_serialize,
      beforeSubmit : test_form_avant_envoi,
      error : retour_form_erreur,
      success : retour_form_valide
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        if (!please_wait)
        {
          $(this).ajaxSubmit(ajaxOptions);
          return false;
        }
        else
        {
          return false;
        }
      }
    );

    // Fonction précédent le traitement du formulaire (avec jquery.form.js)
    function action_form_avant_serialize(jqForm, options)
    {
      if($('#box_visible').is(':checked'))
      {
        // Obligé rajouter le test à ce niveau car si la date a été changé depuis le calendrier, l'événement change() n'a pas été déclenché (et dans test_form_avant_envoi() c'est trop tard).
        $('#f_date_visible').val($('#f_date').val());
      }
      if($('#box_autoeval').is(':checked'))
      {
        $('#f_date_autoeval').val('00/00/0000');
      }
    }

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg_gestion').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        please_wait = true;
        $('#form_gestion button').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
      $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
      please_wait = false;
      $('#form_gestion button').prop('disabled',false);
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
            $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
          case 'dupliquer':
            var new_tds = responseJSON['html'];
            if(TYPE=='groupe')
            {
              var groupe_id = $("#f_groupe option:selected").val();
              var new_tds = new_tds.replace('>{{GROUPE_NOM}}<','>'+tab_groupe[groupe_id]+'<'); // on ne prend pas '<td>' en entier car il y a un attribut class
            }
            var new_tr = '<tr class="new">'+new_tds+'</tr>';
            $('#table_action tbody').prepend(new_tr);
            eval( responseJSON['script'] );
            break;
          case 'modifier':
            var new_tds = responseJSON['html'];
            if(TYPE=='groupe')
            {
              var groupe_id = $("#f_groupe option:selected").val();
              var new_tds = new_tds.replace('>{{GROUPE_NOM}}<','>'+tab_groupe[groupe_id]+'<'); // on ne prend pas '<td>' en entier car il y a un attribut class
            }
            $('#devoir_'+$('#f_ref').val()).parent().addClass("new").html(new_tds);
            eval( responseJSON['script'] );
            break;
          case 'supprimer':
            $('#devoir_'+$('#f_ref').val()).parent().remove();
            break;
        }
        tableau_maj();
        $.fancybox.close();
        mode = false;
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur le bouton pour envoyer un import csv (saisie déportée, formulaire #zone_deport_archivage)
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_importer_scores = $('#zone_deport_archivage');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_importer_scores =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_deport_archivage",
      error : retour_form_erreur_importer_scores,
      success : retour_form_valide_importer_scores
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_importer_scores').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg_deport_archivage').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.csv.txt.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#ajax_msg_deport_archivage').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "csv" ou "txt".');
            return false;
          }
          else
          {
            $('#f_archivage_action').val('importer_saisie_csv');
            $('#zone_deport_archivage button').prop('disabled',true);
            $('#ajax_msg_deport_archivage').attr('class','loader').html("En cours&hellip;");
            formulaire_importer_scores.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_importer_scores.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_importer_scores);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_importer_scores(jqXHR, textStatus, errorThrown)
    {
      $('#f_importer_scores').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#zone_deport_archivage button').prop('disabled',false);
      $('#ajax_msg_deport_archivage').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_importer_scores(responseJSON)
    {
      $('#f_importer_scores').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#zone_deport_archivage button').prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_deport_archivage').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        var nb_notes_remontees = 0;
        var nb_notes_reportees = 0;
        for(i in responseJSON)
        {
          if( i != 'statut' )
          {
            tab_valeur = responseJSON[i].split('.');
            if(tab_valeur.length==3)
            {
              nb_notes_remontees++;
              var eleve_id = tab_valeur[0];
              var item_id  = tab_valeur[1];
              var score    = tab_valeur[2];
              champ = $table_saisir_voir.find('input[name='+item_id+'x'+eleve_id+']');
              if(champ.length)
              {
                nb_notes_reportees++;
                switch (score)
                {
                  case 'A': champ.val('AB').attr('class','AB'); break;
                  case 'D': champ.val('DI').attr('class','DI'); break;
                  case 'N': champ.val('NN').attr('class','NN'); break;
                  case 'E': champ.val('NE').attr('class','NE'); break;
                  case 'F': champ.val('NF').attr('class','NF'); break;
                  case 'R': champ.val('NR').attr('class','NR'); break;
                  case 'P': champ.val('PA').attr('class','PA'); break;
                  default : champ.val(score).attr('class','N'+score); break;
                }
                champ.parent().css("background-color","#F6D");
              }
              if(modification==false)
              {
                $(window).on('beforeunload', confirmOnLeave );
                modification = true;
              }
            }
          }
        }
        var s_remontees = (nb_notes_remontees>1) ? 's' : '' ;
        var s_reportees = (nb_notes_remontees>1) ? 's' : '' ;
        $('#ajax_msg_deport_archivage').attr('class','valide').html(nb_notes_remontees+" saisie"+s_remontees+" trouvée"+s_remontees+" dans le fichier ; "+nb_notes_reportees+" note"+s_reportees+" reportée"+s_reportees+" dans le tableau.<br />N'oubliez pas d'enregistrer !");
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire zone_upload
    // Traitement du clic sur un bouton pour envoyer un sujet ou un corrigé de devoir
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_document = $('#zone_upload');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_document =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_document_upload",
      error : retour_form_erreur_document,
      success : retour_form_valide_document
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_uploader_sujet , #f_uploader_corrige').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_document_upload').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.bat.com.exe.php.zip.'.indexOf('.'+fichier_ext+'.') !== -1 )
          {
            $('#ajax_document_upload').attr('class','erreur').html('Extension du fichier "'+fichier_nom+'" interdite.');
            return false;
          }
          else
          {
            var doc_objet = $(this).attr('id').substring(11); // f_uploader_*
            $('#f_doc_objet').val(doc_objet);
            $('#zone_upload button').prop('disabled',true);
            $('#ajax_document_upload').attr('class','loader').html("En cours&hellip;");
            formulaire_document.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_document.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_document);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_document(jqXHR, textStatus, errorThrown)
    {
      $('#f_uploader_sujet').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#f_uploader_corrige').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      activer_boutons_upload( $('#uploader_ref').val() );
      $('#ajax_document_upload').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_document(responseJSON)
    {
      $('#f_uploader_sujet').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $('#f_uploader_corrige').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      if(responseJSON['statut']==false)
      {
        $('#ajax_document_upload').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_document_upload').attr('class','valide').html("Document enregistré.");
        var ref   = responseJSON['ref'];
        var objet = responseJSON['objet'];
        var url   = responseJSON['url'];
        var ext   = url.split('.').pop().toLowerCase();
        if(objet=='sujet') { var alt='sujet';   var title='Sujet';   var numero=0; tab_sujets[ref]   = url; }
        else               { var alt='corrigé'; var title='Corrigé'; var numero=1; tab_corriges[ref] = url; }
        var lien        = '<a href="'+url+'" target="_blank" rel="noopener noreferrer" class="no_puce"><img alt="'+alt+'" src="./_img/document/'+objet+'_oui.png" title="'+title+' disponible." /></a>';
        $('#span_'+objet).html(lien);
        $('#devoir_'+ref).parent().addClass("new");
        $('#devoir_'+ref).prev().prev().children().eq(numero).replaceWith(lien);
        if ( '.doc.docx.odg.odp.ods.odt.ppt.pptx.rtf.sxc.sxd.sxi.sxw.xls.xlsx.'.indexOf('.'+ext+'.') !== -1 )
        {
          $.prompt(
            "Votre fichier a bien été joint au devoir.<br />Néanmoins, pour être consulté, il nécessite un ordinateur équipé d'une suite bureautique adaptée.<br />Pour une meilleure accessibilité, il serait préférable de le convertir au format PDF.",
            {
              title  : 'Information'
            }
          );
        }
      }
      activer_boutons_upload( $('#uploader_ref').val() );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un bouton pour retirer un sujet ou un corrigé de devoir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_supprimer_sujet , #bouton_supprimer_corrige').click
    (
      function()
      {
        $('#zone_upload button').prop('disabled',true);
        $('#ajax_document_upload').attr('class','loader').html("En cours&hellip;");
        var objet = $(this).attr('id').substring(17);
        var ref   = $('#uploader_ref').val();
        var url   = (objet=='sujet') ? tab_sujets[ref] : tab_corriges[ref] ;
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=retirer_document'+'&f_doc_objet='+objet+'&f_ref='+ref+'&f_doc_url='+encodeURIComponent(url),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_document_upload').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              activer_boutons_upload(ref);
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==false)
              {
                $('#ajax_document_upload').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_document_upload').attr('class','valide').html("Document retiré.");
                if(objet=='sujet') { var alt='sujet';   var numero=0; tab_sujets[ref] = ''; }
                else               { var alt='corrigé'; var numero=1; tab_corriges[ref] = ''; }
                var lien        = '<img alt="'+alt+'" src="./_img/document/'+objet+'_non.png" />';
                $('#span_'+objet).html(lien);
                $('#devoir_'+ref).parent().addClass("new");
                $('#devoir_'+ref).prev().prev().children().eq(numero).replaceWith(lien);
              }
              activer_boutons_upload(ref);
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un bouton pour référencer un lien de sujet ou corrigé de devoir
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_referencer_sujet , #bouton_referencer_corrige').click
    (
      function()
      {
        var objet = $(this).attr('id').substring(18);
        var ref   = $('#uploader_ref').val();
        var url   = $('#f_adresse_'+objet).val();
        if(url == '')
        {
          $('#ajax_document_upload').attr('class','erreur').html("Adresse manquante !");
          $('#f_adresse_'+objet).focus();
          return false;
        }
        else if(!testURL(url))
        {
          $('#ajax_document_upload').attr('class','erreur').html("Adresse incorrecte !");
          $('#f_adresse_'+objet).focus();
          return false;
        }
        else
        {
          $('#zone_upload button').prop('disabled',true);
          $('#ajax_document_upload').attr('class','loader').html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action=referencer_document'+'&f_doc_objet='+objet+'&f_ref='+ref+'&f_doc_url='+encodeURIComponent(url),
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_document_upload').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
                activer_boutons_upload(ref);
                return false;
              },
              success : function(responseJSON)
              {
                initialiser_compteur();
                if(responseJSON['statut']==false)
                {
                  $('#ajax_document_upload').attr('class','alerte').html(responseJSON['value']);
                }
                else
                {
                  $('#ajax_document_upload').attr('class','valide').html("Document référencé.");
                  if(objet=='sujet') { var alt='sujet';   var title='Sujet';   var numero=0; tab_sujets[ref] = url; }
                  else               { var alt='corrigé'; var title='Corrigé'; var numero=1; tab_corriges[ref] = url; }
                  var lien        = '<a href="'+url+'" target="_blank" rel="noopener noreferrer" class="no_puce"><img alt="'+alt+'" src="./_img/document/'+objet+'_oui.png" title="'+title+' disponible." /></a>';
                  $('#span_'+objet).html(lien);
                  $('#devoir_'+ref).parent().addClass("new");
                  $('#devoir_'+ref).prev().prev().children().eq(numero).replaceWith(lien);
                }
                activer_boutons_upload(ref);
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du clic sur un checkbox pour déclarer (ou pas) une évaluation complète en saisie
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on
    (
      'click',
      'a.fini',
      function()
      {
        var obj_lien = $(this);
        var txt_span = obj_lien.children('span').text();
        var txt_i    = obj_lien.children('i').text();
        var fini     = (txt_i=='terminé') ? 'oui' : 'non' ;
        var ref      = obj_lien.parent().next().attr('id').substring(7); // "devoir_" + ref
        $.ajax
        (
          {
            type : 'POST',
            url  : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=maj_fini'+'&f_fini='+fini+'&f_ref='+ref,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
              return false;
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
                $('#devoir_'+ref).parent().addClass("new");
                if(fini=='oui') { obj_lien.html('<span>'+txt_i+'</span><i>'+txt_span+'</i>').parent().addClass("bf"); }
                else            { obj_lien.html('<span>'+txt_i+'</span><i>'+txt_span+'</i>').parent().removeClass("bf"); }
              }
              return false;
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonctions pour le traitement audio
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Article initial :
     * @see http://webaudiodemos.appspot.com/AudioRecorder/index.html 
     *
     * Ressource dont est issu le code utilisé :
     * @see http://audior.ec/blog/recording-mp3-using-only-html5-and-javascript-recordmp3-js/
     * @see http://audior.ec/recordmp3js/
     * @see https://github.com/nusofthq/Recordmp3js
     * (utilise le même plugin mais en le couplant à http://lame.sourceforge.net/ qu'il a converti en js grâce à https://github.com/kripken/emscripten)
     *
     * Autre développement similaire :
     * @see https://github.com/welll/record-encode-audio-from-browser
     * (non testé, mais semble ne fonctionner ni mieux ni moins bien)
     */

    var audio_context;
    var recorder;

    function startUserMedia(stream)
    {
      var input = audio_context.createMediaStreamSource(stream);
      log('log',"Flux de médias créé." );
      log('log',"Fréquence d'échantillonnage d'entrée : " +input.context.sampleRate);
      input.connect(audio_context.destination);
      log('log',"Entrée connectée au contexte audio.");
      recorder = new Recorder( input, { numChannels: 1 } );
      log('log',"Enregistreur initialisé.");
      $('#record_start').show();
      $('#ajax_msg_enregistrer_audio').removeAttr('class').html("");
    }

    function initAudioContext()
    {
      try {
        // webkit shim
        window.AudioContext = window.AudioContext || window.webkitAudioContext || window.mozAudioContext ;
        navigator.getUserMedia = ( navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia );
        window.URL = window.URL || window.webkitURL;
        audio_context = new AudioContext;
        log('log',"Mise en place du contexte audio.");
        if(navigator.getUserMedia)
        {
          log('log',"Environnement navigator.getUserMedia disponible.");
          $('#ajax_msg_enregistrer_audio').attr('class','alerte').html("Veuillez autoriser l'utilisation du microphone (voir en haut de la fenêtre)&hellip;");
          navigator.getUserMedia(
            {audio: true},
            startUserMedia,
            function(e)
            {
              log('error',"Pas d'entrée audio en direct : " + e);
              $('#ajax_msg_enregistrer_audio').attr('class','erreur').html("Utilisation du microphone rejetée&hellip; Veuillez l'autoriser puis recharger la page.");
              $('#record_start').hide();
            }
          );
        }
        else
        {
          log('warn',"Environnement navigator.getUserMedia manquant !");
          $('#ajax_msg_enregistrer_audio').attr('class','erreur').html("Ce navigateur ne gère pas l'enregistrement audio !");
        }
      } catch (e) {
        log('error',"Ce navigateur ne gère pas l'enregistrement audio !");
        $('#ajax_msg_enregistrer_audio').attr('class','erreur').html("Ce navigateur ne gère pas l'enregistrement audio !");
        $('#record_start').hide();
      }
    };

    if(typeof(Worker)!=='undefined') // Pour éviter une erreur js avec IE8, par exemple...
    {

      var WORKER_PATH = './_js/audiorecord_recorderWorker.js';
      var encoderWorker = new Worker('./_js/audiorecord_mp3Worker.js');

      var Recorder = function(source, cfg){
        var config = cfg || {};
        var bufferLen = config.bufferLen || 4096;
        var numChannels = config.numChannels || 2;
        this.context = source.context;
        this.node = (this.context.createScriptProcessor || this.context.createJavaScriptNode).call(this.context, bufferLen, numChannels, numChannels);
        var worker = new Worker(config.workerPath || WORKER_PATH);
        worker.postMessage({
          command: 'init',
          config: {
            sampleRate: this.context.sampleRate,
            numChannels: numChannels
          }
        });
        var recording = false,
          currCallback;

        this.node.onaudioprocess = function(e){
          if (!recording) return;
          var buffer = [];
          for (var channel = 0; channel < numChannels; channel++){
            buffer.push(e.inputBuffer.getChannelData(channel));
          }
          worker.postMessage({
            command: 'record',
            buffer: buffer
          });
        };

        this.configure = function(cfg){
          for (var prop in cfg){
            if (cfg.hasOwnProperty(prop)){
              config[prop] = cfg[prop];
            }
          }
        };

        this.record = function(){
          recording = true;
        };

        this.stop = function(){
          recording = false;
        };

        this.clear = function(){
          worker.postMessage({ command: 'clear' });
        };

        this.getBuffer = function(cb) {
          currCallback = cb || config.callback;
          worker.postMessage({ command: 'getBuffer' })
        };

        this.exportWAV = function(cb, type){
          currCallback = cb || config.callback;
          type = type || config.type || 'audio/wav' ;
          if (!currCallback) throw new Error('Callback not set');
          worker.postMessage({
            command: 'exportWAV',
            type: type
          });
        };

        // Mp3 conversion
        worker.onmessage = function(e){
          var blob = e.data;
          log('log',"Objet Blob de taille " + blob.size + " et de type " + blob.type);

          var arrayBuffer;
          var fileReader = new FileReader();

          fileReader.onload = function(){
            arrayBuffer = this.result;
            var buffer = new Uint8Array(arrayBuffer), data = parseWav(buffer);

            log('log',data);
            log('log',"Conversion au format MP3 en cours.");
            $('#ajax_msg_enregistrer_audio').html("Traitement du signal enregistré en cours&hellip;");

            encoderWorker.postMessage({
              cmd: 'init',
              config:{
                mode : 3,
                channels:1,
                samplerate: data.sampleRate,
                bitrate: data.bitsPerSample
              }
            });

            encoderWorker.postMessage({ cmd: 'encode', buf: Uint8ArrayToFloat32Array(data.samples) });
            encoderWorker.postMessage({ cmd: 'finish'});
            encoderWorker.onmessage = function(e) {
              if (e.data.cmd == 'data') {
                log('log',"Conversion au format MP3 terminée.");
                var mp3Blob = new Blob([new Uint8Array(e.data.buf)], {type: 'audio/mp3'});
                uploadAudio(mp3Blob);
                var url = 'data:audio/mp3;base64,'+encode64(e.data.buf);
                $('#audio_lecture').attr('src',url);
              }
            };
          };

          fileReader.readAsArrayBuffer(blob);
          currCallback(blob);
        };

        function encode64(buffer) {
          var binary = '',
            bytes = new Uint8Array( buffer ),
            len = bytes.byteLength;
          for (var i = 0; i < len; i++) {
            binary += String.fromCharCode( bytes[ i ] );
          }
          return window.btoa( binary );
        }

        function parseWav(wav) {
          function readInt(i, bytes) {
            var ret = 0, shft = 0;
            while (bytes) {
              ret += wav[i] << shft;
              shft += 8;
              i++;
              bytes--;
            }
            return ret;
          }
          if (readInt(20, 2) != 1) throw 'Invalid compression code, not PCM';
          if (readInt(22, 2) != 1) throw 'Invalid number of channels, not 1';
          return {
            sampleRate: readInt(24, 4),
            bitsPerSample: readInt(34, 2),
            samples: wav.subarray(44)
          };
        }

        function Uint8ArrayToFloat32Array(u8a){
          var f32Buffer = new Float32Array(u8a.length);
          for (var i = 0; i < u8a.length; i++) {
            var value = u8a[i<<1] + (u8a[(i<<1)+1]<<8);
            if (value >= 0x8000) value |= ~0x7FFF;
            f32Buffer[i] = value / 0x8000;
          }
          return f32Buffer;
        }

        function uploadAudio(mp3Data){
          var reader = new FileReader();
          reader.onload = function(event){
            $('#enregistrer_audio_msg_data').val(event.target.result);
            $('#ajax_msg_enregistrer_audio').html("Transfert des données vers le serveur&hellip;");
            valider_enregistrer_audio(true);
          };
          reader.readAsDataURL(mp3Data);
        }

        source.connect(this.node);
        this.node.connect(this.context.destination); //this should not be necessary
      };

      window.Recorder = Recorder;
    }
    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fonctions gérant le décompte restant pour un enregistrement audio
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function audio_compteur_play()
    {
      audio_duree_restante--;
      if(audio_duree_restante>0)
      {
        $('#ajax_msg_enregistrer_audio').attr('class','valide').html("Enregistrement en cours&hellip; Encore "+audio_duree_restante+"s maximum.");
      }
      else
      {
        $('#audio_enregistrer_stop').click();
      }
    }

    function audio_compteur_stop()
    {
      $('#ajax_msg_enregistrer_audio').stopTime('audio_record');
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur une image pour ajouter ou modifier un commentaire audio ou texte
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_zone_enregistrer( msg_objet , msg_url , msg_data )
    {
      $('#enregistrer_'+msg_objet+'_msg_url').val( msg_url );
      if(msg_objet=='audio')
      {
        if(msg_data)
        {
          $('#audio_lecture').attr('src',msg_data);
          $('#record_play, #record_delete').show();
        }
        else
        {
          $('#record_play, #record_delete').hide();
        }
        $('#record_stop').hide();
        audio_context || initAudioContext();
        $('#fermer_enregistrer_audio').prop('disabled',false);
      }
      // Afficher la zone
      $.fancybox( { 'href':'#zone_enregistrer_'+msg_objet , onStart:function(){$('#zone_enregistrer_'+msg_objet).css("display","block");} , onClosed:function(){$('#zone_enregistrer_'+msg_objet).css("display","none");} , 'minWidth':600 , 'modal':true , 'centerOnScroll':true } );
      if(msg_objet=='texte')
      {
        $('#f_msg_texte').focus().val(msg_data);
        afficher_textarea_reste( $('#f_msg_texte') , nb_caracteres_max );
      }
    }

    $table_saisir_voir.on
    (
      'click',
      'q',
      function()
      {
        var classe = $(this).attr('class');
        if( (classe=='texte_consulter_non') || (classe=='audio_ecouter_non') )
        {
          return false;
        }
        // Récupérer les informations
        var tab_infos = $(this).parent().attr('id').split('_');
        var msg_objet = tab_infos[0]; // texte | audio
        var user_id   = tab_infos[1];
        var user_nom  = $('#image_'+user_id).attr('alt');
        var obj_autre = (msg_objet=='texte') ? 'audio' : 'texte' ;
        var msg_autre = ( $('#'+obj_autre+'_'+user_id).hasClass('off') ) ? 'oui' : 'non' ;
        // Les reporter
        $('#titre_voir_commentaires').html(user_nom);
        $('#titre_enregistrer_'+msg_objet).html(user_nom);
        $('#enregistrer_'+msg_objet+'_ref').val( $('#saisir_voir_ref').val() );
        $('#enregistrer_'+msg_objet+'_eleve_id').val( user_id );
        $('#enregistrer_'+msg_objet+'_msg_autre').val( msg_autre );
        // Récupérer les items et les notes pour les avoir sous les yeux
        if(mode=='saisir')
        {
          var tab_report = new Array();
          // Récupérer les données des items
          tab_report['item'] = new Array();
          $table_saisir_voir.find("tbody th").each
          (
            function()
            {
              tab_report['item'].push( $(this).children('b').text()+' '+$(this).children('div').text() );
            }
          );
          // Récupérer les saisies de l'élève
          tab_report['eleve'] = new Array();
          var id_colonne = $(this).attr('id').substring(5); // texteCiL | audioCiL
          $table_saisir_voir.find("tbody td input[id^="+id_colonne+"]").each
          (
            function()
            {
              tab_report['eleve'].push( $(this).attr('class') );
            }
          );
          // On concatène tout ça
          var table_report = '<table class="scor_eval"><tbody class="h">';
          var i;
          for( i=0 ; i<tab_report['eleve'].length ; i+=1 )
          {
            table_report += '<tr><td><input type="text" class="'+tab_report['eleve'][i]+'" readonly /></td><th>'+tab_report['item'][i]+'</th></tr>';
          }
          table_report += '</tbody></table>';
          $('#report_tableau_'+msg_objet).html(table_report);
        }
        else
        {
          $('#report_tableau_'+msg_objet).html('');
        }
        // Récupérer (si besoin) le texte ou l'audio actuellement enregistré
        if(mode=='saisir')
        {
          if( $(this).parent().hasClass('off') )
          {
            $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
            $.ajax
            (
              {
                type : 'POST',
                url : 'ajax.php?page='+PAGE,
                data : 'csrf='+CSRF+'&f_action='+'recuperer_message'+'&f_ref='+$('#saisir_voir_ref').val()+'&f_eleve_id='+user_id+'&f_msg_objet='+msg_objet,
                dataType : 'json',
                error : function(jqXHR, textStatus, errorThrown)
                {
                  $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
                  return false;
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
                    afficher_zone_enregistrer( msg_objet , responseJSON['msg_url'] , responseJSON['msg_data'] );
                  }
                }
              }
            );
          }
          else
          {
            afficher_zone_enregistrer( msg_objet , '' /*msg_url*/ , '' /*msg_data*/ );
          }
        }
        else if(mode=='voir')
        {
          // Récupérer le texte ou l'audio actuellement enregistré
          $.fancybox( '<label class="loader">'+'En cours&hellip;'+'</label>' , {'centerOnScroll':true} );
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_action='+'recuperer_message'+'&f_ref='+$('#saisir_voir_ref').val()+'&f_eleve_id='+user_id+'&f_msg_objet='+msg_objet,
              dataType : 'json',
              error : function(jqXHR, textStatus, errorThrown)
              {
                $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+'</label>' , {'centerOnScroll':true} );
                return false;
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
                  if(msg_objet=='texte')
                  {
                    $('#f_voir_texte').val(responseJSON['msg_data']);
                    $('#report_texte').show();
                    $('#report_audio').hide();
                  }
                  else
                  {
                    $('#f_ecouter_audio').attr('src',responseJSON['msg_data']);
                    $('#report_audio').show();
                    $('#report_texte').hide();
                  }
                  // Afficher la zone
                  $.fancybox( { 'href':'#zone_voir_commentaires' , 'centerOnScroll':true } );
                  if(msg_objet=='audio')
                  {
                    document.getElementById("f_ecouter_audio").play();
                  }
                }
              }
            }
          );
        }
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_enregistrer_texte').on
    (
      'keyup',
      '#f_msg_texte',
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Valider ou Annuler la saisie d'un commentaire texte
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function fermer_zone_enregistrer_texte()
    {
      $('#titre_enregistrer_texte').html("");
      $('#ajax_msg_enregistrer_texte').removeAttr('class').html("");
      $.fancybox.close();
    }

    $('#annuler_enregistrer_texte').click
    (
      function()
      {
        fermer_zone_enregistrer_texte();
      }
    );

    $('#valider_enregistrer_texte').click
    (
      function()
      {
        var ref = $('#enregistrer_texte_ref').val();
        $('#zone_enregistrer_texte button').prop('disabled',true);
        $('#ajax_msg_enregistrer_texte').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=enregistrer_texte'+'&f_prof_liste='+tab_profs[ref]+'&f_date_visible='+$("#saisir_voir_date_visible").val()+'&f_description='+encodeURIComponent($("#saisir_voir_description").val())+'&'+$("#zone_enregistrer_texte").serialize(),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#zone_enregistrer_texte button').prop('disabled',false);
              $('#ajax_msg_enregistrer_texte').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#zone_enregistrer_texte button').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg_enregistrer_texte').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg_enregistrer_texte').attr('class','valide').html("Commentaire enregistré !");
                var eleve_id = $('#enregistrer_texte_eleve_id').val();
                if(responseJSON['value']=='supprimé')
                {
                  $('#texte_'+eleve_id).removeAttr('class').children('q').attr('title',"Saisir un commentaire écrit.");
                }
                else
                {
                  $('#enregistrer_texte_msg_url').val(responseJSON['value']);
                  $('#texte_'+eleve_id).addClass('off').children('q').attr('title',"Modifier le commentaire écrit.");
                }
                fermer_zone_enregistrer_texte();
              }
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Envoyer un commentaire audio
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function valider_enregistrer_audio(is_audio)
    {
      var ref = $('#enregistrer_audio_ref').val();
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=enregistrer_audio'+'&f_prof_liste='+tab_profs[ref]+'&f_date_visible='+$("#saisir_voir_date_visible").val()+'&f_description='+encodeURIComponent($("#saisir_voir_description").val())+'&'+$("#zone_enregistrer_audio").serialize(),
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_enregistrer_audio').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            $('#fermer_enregistrer_audio').prop('disabled',false);
            if(is_audio)
            {
              $('#audio_enregistrer_stop').prop('disabled',false);
              $('#record_stop').hide();
              $('#record_start').show();
            }
            else
            {
              $('#record_start , #record_play, #record_delete').show();
            }
            return false;
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            $('#fermer_enregistrer_audio').prop('disabled',false);
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg_enregistrer_audio').attr('class','alerte').html(responseJSON['value']);
              if(is_audio)
              {
                $('#audio_enregistrer_stop').prop('disabled',false);
                $('#record_stop').hide();
                $('#record_start').show();
              }
              else
              {
                $('#record_start , #record_play, #record_delete').show();
              }
            }
            else
            {
              var eleve_id = $('#enregistrer_audio_eleve_id').val();
             if(responseJSON['value']=='supprimé')
              {
                $('#audio_'+eleve_id).removeAttr('class').children('q').attr('title',"Enregistrer un commentaire audio.");
                $('#ajax_msg_enregistrer_audio').attr('class','valide').html("Commentaire supprimé !");
                $('#record_start').show();
              }
              else
              {
                $('#enregistrer_audio_msg_url').val(responseJSON['value']);
                $('#audio_'+eleve_id).addClass('off').children('q').attr('title',"Modifier le commentaire audio.");
                $('#ajax_msg_enregistrer_audio').attr('class','valide').html("Commentaire enregistré !");
                $('#audio_enregistrer_stop').prop('disabled',false);
                $('#record_stop').hide();
                $('#record_start , #record_play, #record_delete').show();
              }
            }
          }
        }
      );
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Supprimer un commentaire audio
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#audio_enregistrer_supprimer').click
    (
      function()
      {
        $('#enregistrer_audio_msg_data').val('');
        $('#record_start , #record_play, #record_delete').hide();
        $('#ajax_msg_enregistrer_audio').attr('class','loader').html("Suppression en cours&hellip;");
        valider_enregistrer_audio(false);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Actions sur les boutons audio
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#audio_enregistrer_start').click
    (
      function()
      {
        $('#record_start , #record_play , #record_delete').hide();
        $('#fermer_enregistrer_audio').prop('disabled',true);
        $('#record_stop').show();
        // Go
        recorder && recorder.record();
        log('log','Enregistrement en cours...');
        audio_duree_restante = AUDIO_DUREE_MAX;
        audio_compteur_play();
        $('#ajax_msg_enregistrer_audio').everyTime
        ('1s', 'audio_record' , function()
          {
            audio_compteur_play();
          }
        );
      }
    );

    $('#audio_enregistrer_stop').click
    (
      function()
      {
        audio_compteur_stop();
        $('#audio_enregistrer_stop').prop('disabled',true);
        $('#ajax_msg_enregistrer_audio').attr('class','loader').html("Traitement en cours&hellip;");
        // Go
        recorder && recorder.stop();
        log('log','Enregistrement arrêté.');
        // create WAV download link using audio data blob
        recorder && recorder.exportWAV(
          function(blob) {
            /*
            var url = URL.createObjectURL(blob);
            var li = document.createElement('li');
            var au = document.createElement('audio');
            var hf = document.createElement('a');
            au.controls = true;
            au.src = url;
            hf.href = url;
            hf.download = new Date().toISOString() + '.wav';
            hf.innerHTML = hf.download;
            li.appendChild(au);
            li.appendChild(hf);
            recordingslist.appendChild(li);
            */
          }
        );
        recorder.clear();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Fermer la zone de gestion audio
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#fermer_enregistrer_audio').click
    (
      function()
      {
        $('#titre_enregistrer_audio').html("");
        $('#ajax_msg_enregistrer_audio').removeAttr('class').html("");
        $.fancybox.close();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du premier formulaire pour afficher le tableau avec la liste des évaluations
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Afficher / masquer des options de la grille (uniquement pour un groupe)

    var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

    function view_dates_perso()
    {
      var periode_val = $("#f_aff_periode").val();
      if(periode_val!=0)
      {
        $("#dates_perso").attr("class","hide");
      }
      else
      {
        $("#dates_perso").attr("class","show");
      }
    }

    $('#f_aff_periode').change
    (
      function()
      {
        view_dates_perso();
        autoperiode = false;
        // Soumettre le formulaire
        formulaire_prechoix.submit();
      }
    );

    // Changement de groupe (uniquement pour un groupe)
    // -> desactiver les périodes prédéfinies en cas de groupe de besoin
    // -> choisir automatiquement la meilleure période et chercher les évaluations si un changement manuel de période n'a jamais été effectué

    function modifier_periodes()
    {
      var groupe_type = $("#f_aff_classe option:selected").parent().attr('label');
      $("#f_aff_periode option").each
      (
        function()
        {
          var periode_id = $(this).val();
          // La période personnalisée est tout le temps accessible
          if(periode_id!=0)
          {
            // groupe de besoin -> desactiver les périodes prédéfinies
            if( (typeof(groupe_type)=='undefined') || (groupe_type=='Besoins') )
            {
              $(this).prop('disabled',true);
            }
            // classe ou groupe classique -> toutes périodes accessibles
            else
            {
              $(this).prop('disabled',false);
            }
          }
        }
      );
      // Sélectionner si besoin la période personnalisée
      if( (typeof(groupe_type)=='undefined') || (groupe_type=='Besoins') )
      {
        $("#f_aff_periode option[value=0]").prop('selected',true);
        $("#dates_perso").attr("class","show");
      }
      // Modification automatique du formulaire
      if( (groupe_type=='Classes') || (groupe_type=='Groupes') )
      {
        if(autoperiode)
        {
          // Rechercher automatiquement la meilleure période
          var id_classe = $('#f_aff_classe option:selected').val().substring(1);
          if(typeof(tab_groupe_periode[id_classe])!='undefined')
          {
            for(var id_periode in tab_groupe_periode[id_classe]) // Parcourir un tableau associatif...
            {
              var tab_split = tab_groupe_periode[id_classe][id_periode].split('_');
              if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
              {
                $("#f_aff_periode option[value="+id_periode+"]").prop('selected',true);
                view_dates_perso();
                break;
              }
            }
          }
        }
      }
      // Soumettre le formulaire
      formulaire_prechoix.submit();
    }

    $('#f_aff_classe').change
    (
      function()
      {
        modifier_periodes();
      }
    );

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_prechoix = $('#form_prechoix');

    // Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
    // méthode dateITA déjà ajoutée

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation_prechoix = formulaire_prechoix.validate
    (
      {
        rules :
        {
          f_aff_classe : { required:true },
          f_date_debut : { required:function(){return (TYPE=='selection') || $("#f_aff_periode").val()==0;} , dateITA:true },
          f_date_fin   : { required:function(){return (TYPE=='selection') || $("#f_aff_periode").val()==0;} , dateITA:true }
        },
        messages :
        {
          f_aff_classe : { required:"classe / groupe manquant" },
          f_date_debut : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
          f_date_fin   : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="text") {element.next().after(error);}
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_prechoix =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_prechoix",
      beforeSubmit : test_form_avant_envoi_prechoix,
      error : retour_form_erreur_prechoix,
      success : retour_form_valide_prechoix
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_prechoix.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_prechoix);
        return false;
      }
    );

    // Fonction précédant l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi_prechoix(formData, jqForm, options)
    {
      $('#ajax_msg_prechoix').removeAttr('class').html("");
      var readytogo = validation_prechoix.form();
      if(readytogo)
      {
        $('#ajax_msg_prechoix').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_prechoix(jqXHR, textStatus, errorThrown)
    {
      $('#ajax_msg_prechoix').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_prechoix(responseJSON)
    {
      initialiser_compteur();
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_prechoix').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        $('#ajax_msg_prechoix').attr('class','valide').html("Demande réalisée !").fadeOut(3000,function(){$(this).removeAttr('class').html("").show();});
        $('#table_action tbody').html( responseJSON['html'] );
        eval( responseJSON['script'] );
        tableau_maj();
        if( reception_todo )
        {
          $('q.ajouter').click();
        }
        // Afficher des résultats au chargement
        if(auto_voir_devoir_id && auto_voir_groupe_type && auto_voir_groupe_id)
        {
          if( $('#devoir_'+auto_voir_devoir_id+'_'+auto_voir_groupe_type+auto_voir_groupe_id).length )
          {
            saisir_ou_voir();
          }
          else
          {
            auto_voir_devoir_id = false;
            auto_voir_groupe_id = false;
          }
        }
      }
    }

    // N'afficher les éléments qu'une fois le js bien chargé...
    $('#form_prechoix , #table_action').show('fast');

    // Et charger par défaut les dernières évaluations du prof.
    $('#form_prechoix').submit();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Élement saisissable / déplaçable
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $( "#cadre_tactile" ).draggable({cursor:"move"});

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Récupéré après le chargement de la page car potentiellement lourd pour les directeurs et les PP (bloque l'affichage plusieurs secondes)
    if( (PROFIL_TYPE=='professeur') || (PROFIL_TYPE=='directeur') )
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_load_arborescence',
          data : 'f_objet=referentiels'+'&f_item_comm=1'+'&f_all_if_pp=0',
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#arborescence label').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            return false;
          },
          success : function(responseJSON)
          {
            $('#arborescence').replaceWith(responseJSON['value']);
          }
        }
      );
    }

  }
);

