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

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{12:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[7,0],[8,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Interactivité pour la saisie
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('input[type=text]').focus
    (
      function()
      {
        $(this).prev().prop('checked',true);
      }
    );

    $('input[type=radio]').click
    (
      function()
      {
        $('#ajax_msg').removeAttr('class').html('');
        $(this).next().focus();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Intercepter la soumission du formulaire de recherche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_user_search').submit
    (
      function()
      {
        $("#bouton_chercher").click();
        return false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire de recherche
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#bouton_chercher').click
    (
      function()
      {
        var champ_nom = $("input[name=search_champ]:checked").val();
        if(typeof(champ_nom)=='undefined')
        {
          $('#ajax_msg').attr('class','erreur').html("Aucun critère choisi !");
          return false;
        }
        var champ_val = $("#search_"+champ_nom).val().trim();
        if(champ_val==='')
        {
          $('#ajax_msg').attr('class','erreur').html("Valeur non renseignée !");
          $("#search_"+champ_nom).focus();
          return false;
        }
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $('#bouton_chercher').prop('disabled',true);
        $('#resultat').hide(0);
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=rechercher'+'&champ_nom='+champ_nom+'&champ_val='+encodeURIComponent(champ_val),
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              $('#bouton_chercher').prop('disabled',false);
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              $('#bouton_chercher').prop('disabled',false);
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').attr('class','valide').html("Résultat ci-dessous.");
                $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
                $('#table_action tbody').html(responseJSON['value']);
                tableau_maj();
                $('#resultat').show(0);
              }
            }
          }
        );
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le checkbox pour choisir ou non une date de sortie
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#box_sortie_date').click
    (
      function()
      {
        if($(this).is(':checked'))
        {
          $(this).next().show(0).next().hide(0);
        }
        else
        {
          $(this).next().hide(0).next().show(0).children('input').focus();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( id , id_ent , id_gepi , sconet_id , sconet_num , reference , profil , genre , nom , prenom , login , courriel , sortie_date_fr )
    {
      var tab_genre = { '' : 'I' , 'M.' : 'M' , 'Masculin' : 'M' , 'Mme' : 'F' , 'Féminin' : 'F' };
      var opt_genre = ( typeof(tab_genre[genre]) !== 'undefined' ) ? tab_genre[genre] : 'I' ;
      $('#f_id').val(id);
      $('#f_id_ent').val(id_ent);
      $('#f_id_gepi').val(id_gepi);
      $('#f_sconet_id').val(sconet_id);
      $('#f_sconet_num').val(sconet_num);
      $('#f_reference').val(reference);
      $('#f_profil').val(profil);
      $('#f_genre option[value='+opt_genre+']').prop('selected',true);
      $('#f_nom').val(nom);
      $('#f_prenom').val(prenom);
      $('#f_login').val(login);
      $('#f_courriel').val(courriel);
      // date de sortie
      if(sortie_date_fr=='-')
      {
        $('#box_sortie_date').prop('checked',true).next().show(0);
        $('#f_sortie_date').val(input_date).parent().hide(0);
      }
      else
      {
        $('#box_sortie_date').prop('checked',false).next().hide(0);
        $('#f_sortie_date').val(sortie_date_fr).parent().show(0);
      }
      // pour finir
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
    }

    /**
     * Modifier un élève : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id             = objet_tr.attr('id').substring(3);
      var id_ent         = objet_tds.eq( 0).html();
      var id_gepi        = objet_tds.eq( 1).html();
      var sconet_id      = objet_tds.eq( 2).html();
      var sconet_num     = objet_tds.eq( 3).html();
      var reference      = objet_tds.eq( 4).html();
      var profil         = objet_tds.eq( 5).html();
      var genre          = objet_tds.eq( 6).html();
      var nom            = objet_tds.eq( 7).html();
      var prenom         = objet_tds.eq( 8).html();
      var login          = objet_tds.eq( 9).html();
      var courriel       = objet_tds.eq(10).html();
      var sortie_date_fr = objet_tds.eq(11).html();
      // Retirer une éventuelle balise image présente dans profil
      position_image = profil.indexOf('<');
      if (position_image!=-1)
      {
        profil = profil.substring(0,position_image-1);
      }
      // Afficher le formulaire
      afficher_form_gestion( id , unescapeHtml(id_ent) , unescapeHtml(id_gepi) , sconet_id , sconet_num , unescapeHtml(reference) , profil , unescapeHtml(genre) , unescapeHtml(nom) , unescapeHtml(prenom) , unescapeHtml(login) , unescapeHtml(courriel) , sortie_date_fr );
    };

    /**
     * Annuler une action
     * @return void
     */
    var annuler = function()
    {
      $.fancybox.close();
    };

    /**
     * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
     * @return void
     */
    function intercepter(e)
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.modifier'      , modifier );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'    , function(e){intercepter(e);} );

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
          f_id_ent      : { required:false , maxlength:ID_ENT_LONGUEUR_MAX },
          f_id_gepi     : { required:false , maxlength:ID_GEPI_LONGUEUR_MAX },
          f_sconet_id   : { required:false , digits:true , max:16777215 },
          f_sconet_num  : { required:false , digits:true , max:65535 },
          f_reference   : { required:false , maxlength:11 },
          f_genre       : { required:false },
          f_nom         : { required:true , maxlength:NOM_LONGUEUR_MAX },
          f_prenom      : { required:true , maxlength:PRENOM_LONGUEUR_MAX },
          f_login       : { required:function(){return !$('#box_login').is(':checked');} , maxlength:LOGIN_LONGUEUR_MAX },
          f_courriel    : { required:false , email:true , maxlength:COURRIEL_LONGUEUR_MAX },
          f_sortie_date : { required:function(){return !$('#box_sortie_date').is(':checked');} , dateITA:true }
        },
        messages :
        {
          f_id_ent      : { maxlength:ID_ENT_LONGUEUR_MAX+" caractères maximum" },
          f_id_gepi     : { maxlength:ID_GEPI_LONGUEUR_MAX+" caractères maximum" },
          f_sconet_id   : { digits:"Id Sconet : nombre entier inférieur à 2^24" },
          f_sconet_num  : { digits:"N° Sconet : nombre entier inférieur à 2^16" },
          f_reference   : { maxlength:"référence de 11 caractères maximum" },
          f_genre       : { },
          f_nom         : { required:"nom manquant"    , maxlength:NOM_LONGUEUR_MAX+" caractères maximum" },
          f_prenom      : { required:"prénom manquant" , maxlength:PRENOM_LONGUEUR_MAX+" caractères maximum" },
          f_login       : { required:"login manquant"  , maxlength:LOGIN_LONGUEUR_MAX+" caractères maximum" },
          f_courriel    : { email:"adresse invalide", maxlength:COURRIEL_LONGUEUR_MAX+" caractères maximum" },
          f_sortie_date : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("id")=='f_sortie_date') { element.next().after(error); }
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
        var profil = $('#f_profil').val();
        $('#id_'+$('#f_id').val()).addClass("new").html(responseJSON['value'].replace('{{PROFIL}}',tab_profil[profil]));
        $.fancybox.close();
      }
    }

  }
);
