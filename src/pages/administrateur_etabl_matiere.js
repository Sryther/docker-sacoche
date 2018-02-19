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

    var mode = false;

    // tri du tableau (avec jquery.tablesorter.js).
    $('#zone_partage table.form').tablesorter({ headers:{2:{sorter:false}} });
    $('#zone_perso   table.form').tablesorter({ headers:{2:{sorter:false}} });
    var tableau_tri_partage = function(){ $('#zone_partage table.form').trigger( 'sorton' , [ [[1,0],[0,0]] ] ); };
    var tableau_tri_perso   = function(){ $('#zone_perso   table.form').trigger( 'sorton' , [ [[1,0],[0,0]] ] ); };
    var tableau_maj_partage = function(){ $('#zone_partage table.form').trigger( 'update' , [ true ] ); };
    var tableau_maj_perso   = function(){ $('#zone_perso   table.form').trigger( 'update' , [ true ] ); };
    tableau_tri_partage();
    tableau_tri_perso();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , ref , nom )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_ref').val(ref);
      $('#f_nom').val(nom);
      // pour finir
      if( mode=='ajouter_perso' )
      {
        var matiere_type = 'spécifique' ;
        var titre = "Ajouter une matière "+matiere_type
      }
      else
      {
        var matiere_type = (id>ID_MATIERE_PARTAGEE_MAX) ? 'spécifique' : 'partagée' ;
        var titre = mode[0].toUpperCase() + mode.substring(1) + " une matière "+matiere_type
      }
      $('#form_gestion h2').html(titre);
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete_partage , #gestion_delete_perso').hide(0);
      }
      else if(matiere_type=='spécifique')
      {
        $('#gestion_edit , #gestion_delete_partage').hide(0);
        $('#gestion_delete_identite_perso').html(ref+" "+nom);
        $('#gestion_delete_perso').show(0);
      }
      else if(matiere_type=='partagée')
      {
        $('#gestion_edit , #gestion_delete_perso').hide(0);
        $('#gestion_delete_identite_partage').html(ref+" "+nom);
        $('#gestion_delete_partage').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_ref').focus(); }
    }

    /**
     * Ajouter une matière partagée : affichage du formulaire
     * @return void
     */
    var ajouter_partage = function()
    {
      mode = 'ajouter_partage';
      $('#ajax_msg_recherche').removeAttr('class').html("");
      $('#zone_partage, #zone_perso, #form_move').hide();
      $('#zone_ajout_form').show();
      return false;
    };

    /**
     * Ajouter une matière spécifique : mise en place du formulaire
     * @return void
     */
    var ajouter_perso = function()
    {
      mode = 'ajouter_perso';
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*ref*/ , '' /*nom*/ );
    };

    /**
     * Modifier une matière spécifique : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var ref        = objet_tds.eq(0).html();
      var nom        = objet_tds.eq(1).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(ref) , unescapeHtml(nom) );
    };

    /**
     * Supprimer une matière partagée ou spécifique : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var ref        = objet_tds.eq(0).html();
      var nom        = objet_tds.eq(1).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(ref) , unescapeHtml(nom) );
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

    /**
     * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
     * @return void
     */
    function intercepter_motclef(e)
    {
      if(e.which==13)  // touche entrée
      {
        $('#rechercher_motclef').click();
        return false;
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_partage').on( 'click' , 'q.ajouter'       , ajouter_partage );
    $('#zone_partage').on( 'click' , 'q.supprimer'     , supprimer );
    $('#zone_perso'  ).on( 'click' , 'q.ajouter'       , ajouter_perso );
    $('#zone_perso'  ).on( 'click' , 'q.modifier'      , modifier );
    $('#zone_perso'  ).on( 'click' , 'q.supprimer'     , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input'           , function(e){intercepter(e);} );

    $('#zone_ajout_form').on( 'keyup' , '#f_motclef'   , function(e){intercepter_motclef(e);} );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer le cadre de recherche d'une matière partagée à ajouter
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ajout_annuler').click
    (
      function()
      {
        $('#zone_ajout_form').hide();
        $('#zone_partage, #zone_perso, #form_move').show();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Choix du mode de recherche d'une matière partagée
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_recherche_mode input').click
    (
      function()
      {
        mode = $(this).val();
        $("#f_recherche_resultat").html('<li></li>').hide();
        $('#ajax_msg_recherche').removeAttr('class').html("");
        if(mode=='famille')
        {
          $('#f_famille').find('option:first').prop('selected',true);
          $("#f_recherche_motclef").hide();
          $("#f_recherche_famille").show();
        }
        else if(mode=='motclef')
        {
          $("#f_recherche_famille").hide();
          $("#f_recherche_motclef").show();
          $("#f_motclef").focus();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Actualisation du résultat de la recherche des matières par famille ou mot clef
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_resultat_recherche(data_action,data_parametre)
    {
      $('#ajax_msg_recherche').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&'+data_action+'&'+data_parametre,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_recherche').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==true)
            {
              $('#ajax_msg_recherche').removeAttr('class').html("");
              $('#f_recherche_resultat').html(responseJSON['value']).show();
            }
            else
            {
              $('#ajax_msg_recherche').attr('class','alerte').html(responseJSON['value']);
            }
          }
        }
      );
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement du select f_famille => actualisation du résultat de la recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_famille").change
    (
      function()
      {
        $("#f_recherche_resultat").html('<li></li>').hide();
        var famille_id = parseInt( $("#f_famille option:selected").val() ,10);
        if(famille_id)
        {
          maj_resultat_recherche( 'f_action=recherche_matiere_famille' , 'f_famille='+famille_id )
        }
        else
        {
          $('#ajax_msg_recherche').removeAttr('class').html("");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur bouton rechercher_motclef => actualisation du résultat de la recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#rechercher_motclef').click
    (
      function()
      {
        $("#f_recherche_resultat").html('<li></li>').hide();
        var motclef = $("#f_motclef").val();
        if(motclef!='')
        {
          maj_resultat_recherche( 'f_action=recherche_matiere_motclef' , 'f_motclef='+encodeURIComponent(motclef) )
        }
        else
        {
          $('#ajax_msg_recherche').attr('class',"danger").html("Indiquer des mots clefs !");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour ajouter une matière partagée trouvée suite à une recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_recherche_resultat').on
    (
      'click',
      'q.ajouter',
      function()
      {
        var matiere_id = $(this).attr('id').substr(4); // add_
        $('#ajax_msg_recherche').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=ajouter_partage'+'&f_id='+matiere_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_recherche').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_recherche').attr('class','valide').html("Matière ajoutée.");
                var texte = $('#add_'+matiere_id).parent().text();
                var pos_separe  = (texte.indexOf('|')==-1) ? 0 : texte.lastIndexOf('|')+2 ;
                var pos_par_ouv = texte.lastIndexOf('(');
                var pos_par_fer = texte.lastIndexOf(')');
                var matiere_nom = texte.substring(pos_separe,pos_par_ouv-1);
                var matiere_ref = texte.substring(pos_par_ouv+1,pos_par_fer);
                $('#zone_partage table.form tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
                $('#zone_partage table.form tbody').append('<tr id="id_'+matiere_id+'"><td>'+matiere_ref+'</td><td>'+matiere_nom+'</td><td class="nu"><q class="supprimer" title="Supprimer cette matière."></q></td></tr>');
                $('#add_'+matiere_id).attr('class',"ajouter_non").attr('title',"Matière déjà choisie.");
                tableau_maj_partage();
                $('#f_matiere_avant').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
                $('#f_matiere_apres').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
              }
              else
              {
                $('#ajax_msg_recherche').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour déplacer les référentiels d'une matière vers une autre
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#deplacer_referentiels').click
    (
      function()
      {
        var matiere_id_avant = parseInt( $("#f_matiere_avant option:selected").val() ,10);
        var matiere_id_apres = parseInt( $("#f_matiere_apres option:selected").val() ,10);
        if(!matiere_id_avant)
        {
          $('#ajax_msg_move').attr('class','erreur').html("Sélectionner une ancienne matière !");
          $("#f_matiere_avant").focus();
          return false;
        }
        if(!matiere_id_apres)
        {
          $('#ajax_msg_move').attr('class','erreur').html("Sélectionner une nouvelle matière !");
          $("#f_matiere_apres").focus();
          return false;
        }
        if(matiere_id_avant==matiere_id_apres)
        {
          $('#ajax_msg_move').attr('class','erreur').html("Sélectionner des matières différentes !");
          return false;
        }
        var matiere_nom_avant = $("#f_matiere_avant option:selected").text();
        var matiere_nom_apres = $("#f_matiere_apres option:selected").text();
        $('button').prop('disabled',true);
        $('#ajax_msg_move').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=deplacer_referentiels'+'&f_id_avant='+matiere_id_avant+'&f_id_apres='+matiere_id_apres+'&f_nom_avant='+encodeURIComponent(matiere_nom_avant)+'&f_nom_apres='+encodeURIComponent(matiere_nom_apres),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
              $('#ajax_msg_move').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('button').prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#f_matiere_avant option[value='+matiere_id_avant+']').remove();
                $('#f_matiere_apres option[value='+matiere_id_avant+']').remove();
                $('#f_matiere_apres option[value=0]').prop('selected',true);
                $('#id_'+matiere_id_avant).remove();
                $('#ajax_msg_move').attr('class','valide').html("Transfert effectué.");
              }
              else
              {
                $('#ajax_msg_move').attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $('#form_gestion');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          f_ref : { required:true , maxlength:5 },
          f_nom : { required:true , maxlength:50 }
        },
        messages :
        {
          f_ref : { required:"référence manquante" , maxlength:"5 caractères maximum" },
          f_nom : { required:"nom manquant" , maxlength:"50 caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { $('#ajax_msg').after(error); }
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

    var prompt_etapes_confirmer_suppression = {
      etape_2: {
        title   : 'Demande de confirmation (2/3)',
        html    : "Les éventuels référentiels associés seront supprimés !<br />Les résultats des élèves qui en dépendent seront perdus !<br />Souhaitez-vous vraiment supprimer cette matière ?",
        buttons : {
          "Non, c'est une erreur !" : false ,
          "Oui, je confirme !" : true
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            event.preventDefault();
            $('#prompt_indication').html( $('#f_nom').val() );
            $.prompt.goToState('etape_3');
            return false;
          }
          else {
            annuler();
          }
        }
      },
      etape_3: {
        title   : 'Demande de confirmation (3/3)',
        html    : "Attention : dernière demande de confirmation !!!<br />Êtes-vous bien certain de vouloir supprimer la matière &laquo;&nbsp;"+'<span id="prompt_indication"></span>'+"&nbsp;&raquo; ?<br />Est-ce définitivement votre dernier mot ???",
        buttons : {
          "Oui, j'insiste !" : true ,
          "Non, surtout pas !" : false
        },
        submit  : function(event, value, message, formVals) {
          if(value) {
            formulaire.ajaxSubmit(ajaxOptions); // Pas de $(this) ici...
            return true;
          }
          else {
            annuler();
          }
        }
      }
    };

    // Envoi du formulaire (avec jquery.form.js)
    formulaire.submit
    (
      function()
      {
        if (please_wait)
        {
          return false;
        }
        else if( (mode=='supprimer') && ($('#f_id').val()>ID_MATIERE_PARTAGEE_MAX) )
        {
          $.prompt(prompt_etapes_confirmer_suppression);
        }
        else
        {
          $(this).ajaxSubmit(ajaxOptions);
        }
        return false;
      }
    );

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
          case 'ajouter_perso':
            var matiere_id  = responseJSON['id'];
            var matiere_ref = responseJSON['ref'];
            var matiere_nom = responseJSON['nom'];
            new_tr = '<tr id="id_'+matiere_id+'" class="new"><td>'+matiere_ref+'</td><td>'+matiere_nom+'</td><td class="nu"><q class="modifier" title="Modifier cette matière."></q><q class="supprimer" title="Supprimer cette matière."></q></td></tr>';
            $('#zone_perso table.form tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
            $('#zone_perso table.form tbody').prepend(new_tr);
            $('#f_matiere_avant').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
            $('#f_matiere_apres').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
            break;
          case 'modifier':
            var matiere_id  = responseJSON['id'];
            var matiere_ref = responseJSON['ref'];
            var matiere_nom = responseJSON['nom'];
            new_td = '<td>'+matiere_ref+'</td><td>'+matiere_nom+'</td><td class="nu"><q class="modifier" title="Modifier cette matière."></q><q class="supprimer" title="Supprimer cette matière."></q></td>';
            $('#id_'+matiere_id).addClass("new").html(new_td);
            $('#f_matiere_avant option[value='+matiere_id+']').replaceWith('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
            $('#f_matiere_apres option[value='+matiere_id+']').replaceWith('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
            break;
          case 'supprimer':
            var matiere_id = responseJSON['id'];
            $('#id_'+matiere_id).remove();
            $('#f_matiere_avant option[value='+matiere_id+']').remove();
            $('#f_matiere_apres option[value='+matiere_id+']').remove();
            break;
        }
        tableau_maj_perso();
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
