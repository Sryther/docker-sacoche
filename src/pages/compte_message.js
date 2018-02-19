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
    var nb_caracteres_max = 2000;
    var prefixe_type =
    {
      'nom' :
      {
        'd' : 'Tous',
        'n' : 'Niveau ',
        'c' : 'Classe ',
        'g' : 'Groupe ',
        'b' : 'Besoin '
      },
      'code' :
      {
        'd' : 'all',
        'n' : 'niveau',
        'c' : 'classe',
        'g' : 'groupe',
        'b' : 'besoin'
      }
    };

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{0:{sorter:'date_fr'},1:{sorter:'date_fr'},2:{sorter:false},3:{sorter:false},4:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[1,1],[0,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , date_debut_fr , date_fin_fr , destinataires_nombre , destinataires_liste , message_info , message_contenu )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_debut_date').val(date_debut_fr);
      $('#f_fin_date').val(date_fin_fr);
      $('#f_destinataires_nombre').val(destinataires_nombre);
      $('#f_destinataires_liste').val(destinataires_liste);
      $('#f_message_info').val(message_info);
      $('#f_message_longueur').val(message_contenu.length);
      $('#f_message_contenu').val(message_contenu);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un message d'accueil");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(message_info);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minHeight':300 , 'minWidth':750 , 'centerOnScroll':true } );
    }

    /**
     * Ajouter un message : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , input_date /*date_debut_fr*/ , input_date /*date_fin_fr*/ , 'aucun' /*destinataires_nombre*/ , '' /*destinataires_liste*/ , 'aucun' /*message_info*/ , '' /*message_contenu*/ );
    };

    /**
     * Modifier un message : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr             = $(this).parent().parent();
      var objet_tds            = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id                   = objet_tr.attr('id').substring(3);
      var debut_date_fr        = objet_tds.eq(0).html();
      var fin_date_fr          = objet_tds.eq(1).html();
      var destinataires_nombre = objet_tds.eq(2).html();
      var message_info         = objet_tds.eq(3).text();
      // liste des destinataires et contenu du message
      var destinataires_liste  = tab_destinataires[id];
      var message_contenu      = tab_msg_contenus[id];
      // Afficher le formulaire
      afficher_form_gestion( mode , id , debut_date_fr , fin_date_fr , destinataires_nombre , destinataires_liste , unescapeHtml(message_info) , unescapeHtml(message_contenu) );
    };

    /**
     * Supprimer un message : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr     = $(this).parent().parent();
      var objet_tds    = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tr.attr('id').substring(3);
      var message_info = objet_tds.eq(3).text();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , '' /*date_debut_fr*/ , '' /*date_fin_fr*/ , '' /*destinataires_nombre*/ , '' /*destinataires_liste*/ , unescapeHtml(message_info) , '' /*message_contenu*/ );
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
     * Choisir les destinataires associés à un message : mise en place du formulaire
     * @return void
     */
    var choisir_destinataires = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier").
      var destinataires_liste = $("#f_destinataires_liste").val();
      if(destinataires_liste=='')
      {
        $('#f_destinataires').html('');
        $('#retirer_destinataires').prop('disabled',true);
        $('#valider_destinataires').prop('disabled',true);
      }
      else
      {
        $.fancybox( '<label class="loader">En cours&hellip;</label>' , {'centerOnScroll':true} );
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=afficher_destinataires'+'&f_destinataires_liste='+destinataires_liste,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $.fancybox( '<label class="alerte">'+afficher_json_message_erreur(jqXHR,textStatus)+' Veuillez recommencer.</label>' , {'centerOnScroll':true} );
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#f_destinataires').html(responseJSON['value']);
                var etat_disabled = (responseJSON['value']!='') ? false : true ;
                $('#retirer_destinataires').prop('disabled',etat_disabled);
                $('#valider_destinataires').prop('disabled',etat_disabled);
              }
              else
              {
                $.fancybox( '<label class="alerte">'+responseJSON['value']+'</label>' , {'centerOnScroll':true} );
                return false;
              }
            }
          }
        );
      }
      // Afficher la zone
      $.fancybox( { 'href':'#form_destinataires' , onStart:function(){$('#form_destinataires').css("display","block");} , onClosed:function(){$('#form_destinataires').css("display","none");} , 'modal':true , 'minHeight':300 , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Choisir le contenu d'un message : mise en place du formulaire
     * @return void
     */
    var editer_contenu_message = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier").
      var message_contenu = $("#f_message_contenu").val();
      // Afficher la zone
      $.fancybox( { 'href':'#form_message' , onStart:function(){$('#form_message').css("display","block");} , onClosed:function(){$('#form_message').css("display","none");} , 'modal':true , 'minHeight':300 , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
      $('#f_message').focus().val(unescapeHtml(message_contenu));
      afficher_textarea_reste( $('#f_message') , nb_caracteres_max );
    };

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'       , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'      , modifier );
    $('#table_action').on( 'click' , 'q.supprimer'     , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'click' , 'q.choisir_eleve' , choisir_destinataires );
    $('#form_gestion').on( 'click' , 'q.texte_editer'  , editer_contenu_message );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Indiquer le nombre de caractères restant autorisés dans le textarea
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_message').keyup
    (
      function()
      {
        afficher_textarea_reste( $(this) , nb_caracteres_max );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Mettre à jour le formulaire avec la liste des utilisateurs pour un regroupement et un profil donnés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_affichage()
    {
      // On récupère le profil
      var profil_type = $("#f_profil option:selected").val();
      if(profil_type=='')
      {
        $('#ajax_msg_destinataires').removeAttr('class').html("");
        $('#div_users').hide();
        $('#ajouter_destinataires').prop('disabled',true);
        return false
      }
      // On récupère le regroupement
      if( (profil_type=='professeur') || (profil_type=='eleve') || (profil_type=='parent') )
      {
        $('#div_groupe').show();
        var groupe_val = $("#f_groupe option:selected").val();
      }
      else
      {
        $('#div_groupe').hide();
        var groupe_val = 'd2';
      }
      if(!groupe_val)
      {
        $('#ajax_msg_destinataires').removeAttr('class').html("");
        $('#div_users').hide();
        $('#ajouter_destinataires').prop('disabled',true);
        return false
      }
      // Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
      if(isNaN(parseInt(groupe_val,10)))
      {
        groupe_type = groupe_val.substring(0,1);
        groupe_id   = groupe_val.substring(1);
      }
      // Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
      else
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
        groupe_id   = groupe_val;
      }
      if($('#f_indiv').is(':checked'))
      {
        $('#ajax_msg_destinataires').attr('class','loader').html("En cours&hellip;");
        $('#bilan tbody').html('');
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=afficher_users'+'&f_profil_type='+profil_type+'&f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg_destinataires').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_destinataires').removeAttr('class').html("");
                $('#f_user').html(responseJSON['value']).show();
                $('#span_check').show();
                $('#div_users').show();
                $('#ajouter_destinataires').prop('disabled',false);
              }
              else
              {
                $('#ajax_msg_destinataires').attr('class','alerte').html(responseJSON['value']);
                $('#div_users').hide();
                $('#ajouter_destinataires').prop('disabled',true);
              }
            }
          }
        );
      }
      else
      {
        $('#ajax_msg_destinataires').removeAttr('class').html("");
        $('#f_user').hide();
        $('#span_check').hide();
        $('#div_users').show();
        if($('#f_all').is(':checked'))
        {
          $('#ajouter_destinataires').prop('disabled',false);
        }
      }
    }

    $("#f_profil , #f_groupe , #f_all , #f_indiv").change
    (
      function()
      {
        maj_affichage();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour ajouter des destinataires
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ajouter_destinataires').click
    (
      function()
      {
        var prefixe_profil = $("#f_profil option:selected").text()+' | ';
        if($('#f_all').is(':checked'))
        {
          var profil_type = $("#f_profil option:selected").val();
          if( (profil_type=='professeur') || (profil_type=='eleve') || (profil_type=='parent') )
          {
            var groupe_val = $("#f_groupe option:selected").val();
            // Pour un directeur ou un administrateur, groupe_val est de la forme d3 / n2 / c51 / g44
            if(isNaN(parseInt(groupe_val,10)))
            {
              groupe_type = groupe_val.substring(0,1);
              groupe_id   = groupe_val.substring(1);
            }
            // Pour un professeur, groupe_val est un entier, et il faut récupérer la 1ère lettre du label parent
            else
            {
              groupe_type = $("#f_groupe option:selected").parent().attr('label').substring(0,1).toLowerCase();
              groupe_id   = groupe_val;
            }
            var destinataire_id = profil_type + '_' + prefixe_type.code[groupe_type] + '_' + groupe_id ;
            var destinataire_nom = (groupe_type=='d') ? prefixe_type.nom[groupe_type] : prefixe_type.nom[groupe_type] + $("#f_groupe option:selected").text() ;
          }
          else
          {
            var destinataire_id = profil_type + '_all_2';
            var destinataire_nom = ' Tous';
          }
          if( ! $('#f_destinataires_'+destinataire_id).length )
          {
            $('#f_destinataires').prepend('<label for="f_destinataires_'+destinataire_id+'"><input type="checkbox" value="'+destinataire_id+'" id="f_destinataires_'+destinataire_id+'" name="f_destinataires[]">'+prefixe_profil+destinataire_nom+'</label>');
          }
        }
        else
        {
          $('#f_user input:checked').each
          (
            function()
            {
              var destinataire_id = $(this).val();
              var destinataire_nom = $(this).parent().text();
              if( ! $('#f_destinataires_'+destinataire_id).length )
              {
                $('#f_destinataires').prepend('<label for="f_destinataires_'+destinataire_id+'"><input type="checkbox" value="'+destinataire_id+'" id="f_destinataires_'+destinataire_id+'" name="f_destinataires[]">'+prefixe_profil+destinataire_nom+'</label>');
              }
            }
          );
        }
        var etat_disabled = ($('#f_destinataires').children().length) ? false : true ;
        $('#retirer_destinataires').prop('disabled',etat_disabled);
        $('#valider_destinataires').prop('disabled',etat_disabled);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour retirer des destinataires
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#retirer_destinataires').click
    (
      function()
      {
        $('#f_destinataires input:checked').each
        (
          function()
          {
            $(this).parent().remove();
          }
        );
        var etat_disabled = ($('#f_destinataires').children().length) ? false : true ;
        $('#retirer_destinataires').prop('disabled',etat_disabled);
        $('#valider_destinataires').prop('disabled',etat_disabled);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le choix des destinataires associés à un message
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_destinataires').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $('#f_destinataires input').each
        (
          function()
          {
            var id = $(this).val();
            if(id)
            {
              liste += $(this).val()+',';
              nombre++;
            }
          }
        );
        var destinataires_liste  = liste.substring(0,liste.length-1);
        var destinataires_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' sélections' : nombre+' sélection' ) ;
        $('#f_destinataires_liste').val(destinataires_liste);
        $('#f_destinataires_nombre').val(destinataires_nombre);
        $('#annuler_destinataires').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour valider le contenu d'un message
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_message').click
    (
      function()
      {
        var message_contenu = $("#f_message").val();
        $('#f_message_info').val(message_contenu.substring(0,50));
        $('#f_message_longueur').val(message_contenu.length);
        $('#f_message_contenu').val(message_contenu);
        $('#annuler_message').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Clic sur le bouton pour fermer le cadre des destinataires associés à un message (annuler / retour)
    // Clic sur le bouton pour fermer le cadre de rédaction du contenu d'un message (annuler / retour)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_destinataires , #annuler_message').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minHeight':300 , 'minWidth':750 , 'centerOnScroll':true } );
        return false;
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
          f_debut_date          : { required:true , dateITA:true },
          f_fin_date            : { required:true , dateITA:true },
          f_destinataires_liste : { required:true , maxlength:3000 }, // 100 (nombre) x 30 (longueur max estimée par destinataire)
          f_message_longueur    : { min:1 , range: [15, 1000] },
          f_mode_discret        : { required:false }
        },
        messages :
        {
          f_debut_date          : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
          f_fin_date            : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
          f_destinataires_liste : { required:"destinataire(s) manquant(s)" , maxlength:"trop de sélections : choisir \"Tous (automatique)\" sur des regroupements" },
          f_message_longueur    : { min:"contenu manquant" , range:"contenu insuffisant" },
          f_mode_discret        : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.next().after(error); }
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
      $('#ajax_msg_gestion').removeAttr('class').html("");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#form_gestion button').prop('disabled',true);
        $('#ajax_msg_gestion').attr('class','loader').html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#form_gestion button').prop('disabled',false);
      $('#ajax_msg_gestion').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseJSON)
    {
      initialiser_compteur();
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
            $('#table_action tbody').prepend(responseJSON['html']);
            eval( responseJSON['script'] );
            break;
          case 'modifier':
            $('#id_'+$('#f_id').val()).addClass("new").html(responseJSON['html']);
            eval( responseJSON['script'] );
            break;
          case 'supprimer':
            $('#id_'+$('#f_id').val()).remove();
            break;
        }
        tableau_maj();
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
