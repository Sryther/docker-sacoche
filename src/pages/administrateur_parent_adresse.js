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
    var id = 0;
    var nom_prenom = '';
    var td_resp = false;

    // tri du tableau (avec jquery.tablesorter.js).
    $('#table_action').tablesorter({ headers:{6:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    // tableau_tri(); // Ne pas trier volontairement c'est déjà trié à la sortie PHP et pour la recherche levenshtein il faut conserver un tri élève

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , nom_prenom , ligne1 , ligne2 , ligne3 , ligne4 , code_postal , commune , pays )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#gestion_identite').html(nom_prenom);
      $('#f_ligne1').val(ligne1);
      $('#f_ligne2').val(ligne2);
      $('#f_ligne3').val(ligne3);
      $('#f_ligne4').val(ligne4);
      $('#f_code_postal').val(code_postal);
      $('#f_commune').val(commune);
      $('#f_pays').val(pays);
      // pour finir
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      $('#f_ligne1').focus();
    }

    /**
     * Modifier | Ajouter une adresse : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var reference  = objet_tr.attr('id').substring(3);
          mode       = (reference.substring(0,1)=='M') ? 'modifier' : 'ajouter' ;
          id         = reference.substring(1);
          td_resp    = objet_tds.eq(0);
          nom_prenom = objet_tds.eq(1).html();
      var obj_lignes = objet_tds.eq(2).find('span');
      var code_postal= objet_tds.eq(3).html();
      var commune    = objet_tds.eq(4).html();
      var pays       = objet_tds.eq(5).html();
      // Extirper les 4 lignes d'adresses
      var ligne1     = obj_lignes.eq(0).html();
      var ligne2     = obj_lignes.eq(1).html();
      var ligne3     = obj_lignes.eq(2).html();
      var ligne4     = obj_lignes.eq(3).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(nom_prenom) , unescapeHtml(ligne1) , unescapeHtml(ligne2) , unescapeHtml(ligne3) , unescapeHtml(ligne4) , code_postal , unescapeHtml(commune) , unescapeHtml(pays) );
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
          f_ligne1      : { required:false , maxlength:50 },
          f_ligne2      : { required:false , maxlength:50 },
          f_ligne3      : { required:false , maxlength:50 },
          f_ligne4      : { required:false , maxlength:50 },
          f_code_postal : { required:false , maxlength:10 },
          f_commune     : { required:false , maxlength:45 },
          f_pays        : { required:false , maxlength:35 }
        },
        messages :
        {
          f_ligne1      : { maxlength:"50 caractères maxi par élément d'adresse" },
          f_ligne2      : { maxlength:"50 caractères maxi par élément d'adresse" },
          f_ligne3      : { maxlength:"50 caractères maxi par élément d'adresse" },
          f_ligne4      : { maxlength:"50 caractères maxi par élément d'adresse" },
          f_code_postal : { maxlength:"Code postal : 10 caractères maximum" },
          f_commune     : { maxlength:"Commune : 45 caractères maximum" },
          f_pays        : { maxlength:"Pays : 35 caractères maximum" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element) { element.after(error); }
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
        $('#temp_td').html(td_resp); // Pour ne pas perdre l'objet avec l'infobulle, on est obligé de le copier ailleurs avant le html qui suit.
        switch (mode)
        {
          case 'ajouter':
            $('#id_A'+id).addClass("new").attr('id','id_M'+id).html('<td>'+nom_prenom+'</td>'+responseJSON['value']).prepend( td_resp );
            break;
          case 'modifier':
            $('#id_M'+id).addClass("new").html('<td>'+nom_prenom+'</td>'+responseJSON['value']).prepend( td_resp );
            break;
        }
        $.fancybox.close();
        mode = false;
      }
    }

  }
);
