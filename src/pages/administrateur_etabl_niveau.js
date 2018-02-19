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
    // Pas de tri sur le tableau des niveaux partagés car tri initial sur l'ordre du niveau
    $('#zone_perso   table.form').tablesorter({ headers:{2:{sorter:false}} });
    var tableau_tri_perso   = function(){ $('#zone_perso   table.form').trigger( 'sorton' , [ [[1,0],[0,0]] ] ); };
    var tableau_maj_perso   = function(){ $('#zone_perso   table.form').trigger( 'update' , [ true ] ); };
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
      var niveau_type = (id>ID_NIVEAU_PARTAGE_MAX) ? 'spécifique' : 'partagé' ;
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un niveau "+niveau_type);
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete_partage , #gestion_delete_perso').hide(0);
      }
      else if(niveau_type=='spécifique')
      {
        $('#gestion_edit , #gestion_delete_partage').hide(0);
        $('#gestion_delete_identite_perso').html(ref+" "+nom);
        $('#gestion_delete_perso').show(0);
      }
      else if(niveau_type=='partagé')
      {
        $('#gestion_edit , #gestion_delete_perso').hide(0);
        $('#gestion_delete_identite_partage').html(ref+" "+nom);
        $('#gestion_delete_partage').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
    }

    /**
     * Ajouter un niveau partagé : affichage du formulaire
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
     * Ajouter un niveau spécifique : mise en place du formulaire
     * @return void
     */
    var ajouter_perso = function()
    {
      mode = 'ajouter_perso';
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*ref*/ , '' /*nom*/ );
    };

    /**
     * Modifier un niveau spécifique : mise en place du formulaire
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
     * Supprimer un niveau partagé ou spécifique : mise en place du formulaire
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer le cadre de recherche d'un niveau à ajouter
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ajout_annuler').click
    (
      function()
      {
        $('#zone_ajout_form').hide();
        $('#zone_partage, #zone_perso').show();
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Actualisation du résultat de la recherche des niveaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_resultat_recherche(famille_id)
    {
      $('#ajax_msg_recherche').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=recherche_niveau_famille'+'&f_famille='+famille_id,
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
          maj_resultat_recherche(famille_id)
        }
        else
        {
          $('#ajax_msg_recherche').removeAttr('class').html("");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour ajouter un niveau partagé trouvé suite à une recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_recherche_resultat').on
    (
      'click',
      'q.ajouter',
      function()
      {
        var niveau_id = $(this).attr('id').substr(4); // add_
        $('#ajax_msg_recherche').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=ajouter_partage'+'&f_id='+niveau_id,
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
                $('#ajax_msg_recherche').attr('class','valide').html("Niveau ajouté.");
                var texte = $('#add_'+niveau_id).parent().text();
                var pos_separe  = (texte.indexOf('|')==-1) ? 0 : texte.lastIndexOf('|')+2 ;
                var pos_par_ouv = texte.lastIndexOf('(');
                var pos_par_fer = texte.lastIndexOf(')');
                var niveau_nom  = texte.substring(pos_separe,pos_par_ouv-1);
                var niveau_ref  = texte.substring(pos_par_ouv+1,pos_par_fer);
                $('#zone_partage table.form tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
                $('#zone_partage table.form tbody').append('<tr id="id_'+niveau_id+'"><td>'+niveau_ref+'</td><td>'+niveau_nom+'</td><td class="nu"><q class="supprimer" title="Supprimer ce niveau."></q></td></tr>');
                $('#add_'+niveau_id).attr('class',"ajouter_non").attr('title',"Niveau déjà choisi.");
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
          f_ref : { required:true , maxlength:6 },
          f_nom : { required:true , maxlength:50 }
        },
        messages :
        {
          f_ref : { required:"référence manquante" , maxlength:"6 caractères maximum" },
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
        html    : "Les éventuels référentiels associés seront supprimés !<br />Les résultats des élèves qui en dépendent seront perdus !<br />Souhaitez-vous vraiment supprimer ce niveau ?",
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
        html    : "Attention : dernière demande de confirmation !!!<br />Êtes-vous bien certain de vouloir supprimer le niveau &laquo;&nbsp;"+'<span id="prompt_indication"></span>'+"&nbsp;&raquo; ?<br />Est-ce définitivement votre dernier mot ???",
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
        else if( (mode=='supprimer') && ($('#f_id').val()>ID_NIVEAU_PARTAGE_MAX) )
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
            var niveau_id  = responseJSON['id'];
            var niveau_ref = responseJSON['ref'];
            var niveau_nom = responseJSON['nom'];
            new_tr = '<tr id="id_'+niveau_id+'" class="new"><td>'+niveau_ref+'</td><td>'+niveau_nom+'</td><td class="nu"><q class="modifier" title="Modifier ce niveau."></q><q class="supprimer" title="Supprimer ce niveau."></q></td></tr>';
            $('#zone_perso table.form tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
            $('#zone_perso table.form tbody').prepend(new_tr);
            $('#f_niveau_avant').append('<option value="'+niveau_id+'">'+niveau_nom+' ('+niveau_ref+')</option>');
            $('#f_niveau_apres').append('<option value="'+niveau_id+'">'+niveau_nom+' ('+niveau_ref+')</option>');
            break;
          case 'modifier':
            var niveau_id  = responseJSON['id'];
            var niveau_ref = responseJSON['ref'];
            var niveau_nom = responseJSON['nom'];
            new_td = '<td>'+niveau_ref+'</td><td>'+niveau_nom+'</td><td class="nu"><q class="modifier" title="Modifier ce niveau."></q><q class="supprimer" title="Supprimer ce niveau."></q></td>';
            $('#id_'+niveau_id).addClass("new").html(new_td);
            $('#f_niveau_avant option[value='+niveau_id+']').replaceWith('<option value="'+niveau_id+'">'+niveau_nom+' ('+niveau_ref+')</option>');
            $('#f_niveau_apres option[value='+niveau_id+']').replaceWith('<option value="'+niveau_id+'">'+niveau_nom+' ('+niveau_ref+')</option>');
            break;
          case 'supprimer':
            var niveau_id = responseJSON['id'];
            $('#id_'+niveau_id).remove();
            $('#f_niveau_avant option[value='+niveau_id+']').remove();
            $('#f_niveau_apres option[value='+niveau_id+']').remove();
            break;
        }
        tableau_maj_perso();
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
