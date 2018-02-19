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
    // Actualiser l'affichage des vignettes élèves au changement du select
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_affichage()
    {
      $('#liste_eleves').html('');
      // On récupère le regroupement
      var groupe_val = $("#f_groupe option:selected").val();
      if(!groupe_val)
      {
        $('#ajax_msg').removeAttr('class').html("");
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
      $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=afficher'+'&f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type,
          dataType : 'json',
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
          },
          success : function(responseJSON)
          {
            initialiser_compteur();
            if(responseJSON['statut']==false)
            {
              $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
            }
            else
            {
              $('#ajax_msg').attr('class','valide').html("Demande réalisée !");
              $('#liste_eleves').html(responseJSON['value']);
            }
          }
        }
      );
    }

    $("#f_groupe").change
    (
      function()
      {
        maj_affichage();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire form_photos
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_photos = $('#form_photos');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_photos =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_photos",
      error : retour_form_erreur_photos,
      success : retour_form_valide_photos
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_photos').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg_photos').removeAttr('class').html('');
          return false;
        }
        else
        {
          var masque = $("#f_masque").val();
          // Curieusement, besoin d'échapper l'échappement... (en PHP un échappement simple suffit)
          var reg_filename  = new RegExp("\\[(sconet_id|sconet_num|reference|nom|prenom|login|ent_id)\\]","g");
          var reg_extension = new RegExp("\\.(gif|jpg|jpeg|png)$","g");
          if( (!reg_filename.test(masque)) || (!reg_extension.test(masque)) )
          {
            $('#f_photos').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
            $('#ajax_msg_photos').attr('class','erreur').html('Indiquer correctement la forme des noms des fichiers contenus dans l\'archive.');
            return false;
          }
          else
          {
            var fichier_nom = file.name;
            var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
            if( fichier_ext != 'zip' )
            {
              $('#ajax_msg_photos').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension zip.');
              return false;
            }
            else
            {
              $("button").prop('disabled',true);
              $('#ajax_msg_photos').attr('class','loader').html("En cours&hellip;");
              formulaire_photos.submit();
            }
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_photos.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_photos);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_photos(jqXHR, textStatus, errorThrown)
    {
      $('#f_photos').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("button").prop('disabled',false);
      $('#ajax_msg_photos').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_photos(responseJSON)
    {
      $('#f_photos').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      $("button").prop('disabled',false);
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg_photos').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        $('#ajax_msg_photos').attr('class','valide').html('Demande traitée !');
        $.fancybox( { 'href':responseJSON['value'] , 'type':'iframe' , 'width':'80%' , 'height':'80%' , 'centerOnScroll':true } );
        maj_affichage();
      }
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Traitement du formulaire #form_photo
    // Upload d'un fichier (avec jquery.form.js)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire_photo = $('#form_photo');

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions_photo =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
      type : 'POST',
      dataType : 'json',
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg",
      error : retour_form_erreur_photo,
      success : retour_form_valide_photo
    };

    // Vérifications précédant l'envoi du formulaire, déclenchées au choix d'un fichier
    $('#f_photo').change
    (
      function()
      {
        var file = this.files[0];
        if( typeof(file) == 'undefined' )
        {
          $('#ajax_msg').removeAttr('class').html('');
          return false;
        }
        else
        {
          var fichier_nom = file.name;
          var fichier_ext = fichier_nom.split('.').pop().toLowerCase();
          if( '.gif.jpg.jpeg.png.'.indexOf('.'+fichier_ext+'.') == -1 )
          {
            $('#ajax_msg').attr('class','erreur').html('Le fichier "'+fichier_nom+'" n\'a pas une extension autorisée (gif jpg jpeg png).');
            return false;
          }
          else
          {
            afficher_masquer_images_action('hide');
            $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
            formulaire_photo.submit();
          }
        }
      }
    );

    // Envoi du formulaire (avec jquery.form.js)
    formulaire_photo.submit
    (
      function()
      {
        $(this).ajaxSubmit(ajaxOptions_photo);
        return false;
      }
    );

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur_photo(jqXHR, textStatus, errorThrown)
    {
      $('#f_photo').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      afficher_masquer_images_action('show');
      $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide_photo(responseJSON)
    {
      $('#f_photo').clearFields(); // Sinon si on fournit de nouveau un fichier de même nom alors l'événement change() ne se déclenche pas
      afficher_masquer_images_action('show');
      if(responseJSON['statut']==false)
      {
        $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
      }
      else
      {
        initialiser_compteur();
        var user_id    = responseJSON['user_id'];
        var img_width  = responseJSON['img_width'];
        var img_height = responseJSON['img_height'];
        var img_src    = responseJSON['img_src'];
        $('#ajax_msg').removeAttr('class').html('&nbsp;');
        $('#q_'+user_id).parent().html('<img width="'+img_width+'" height="'+img_height+'" src="'+img_src+'" alt="" /><q class="supprimer" title="Supprimer cette photo (aucune confirmation ne sera demandée)."></q>');
      }
    }

    $('#liste_eleves').on
    (
      'click',
      'q.ajouter',
      function()
      {
        var q_id = $(this).attr('id');
        var user_id = q_id.substring(2); // "q_" + id
        $('#f_user_id').val(user_id);
        $('#f_photo').click();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Appel en ajax pour supprimer une photo
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#liste_eleves').on
    (
      'click',
      'q.supprimer',
      function()
      {
        var memo_div = $(this).parent();
        var user_id = memo_div.parent().attr('id').substring(4); // "div_" + id
        afficher_masquer_images_action('hide');
        $('#ajax_msg').attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=supprimer_photo'+'&f_user_id='+user_id,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('#ajax_msg').attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              afficher_masquer_images_action('show');
              if(responseJSON['statut']==false)
              {
                $('#ajax_msg').attr('class','alerte').html(responseJSON['value']);
              }
              else
              {
                $('#ajax_msg').removeAttr('class').html('');
                memo_div.html('<q id="q_'+user_id+'" class="ajouter" title="Ajouter une photo."></q><img width="1" height="1" src="./_img/auto.gif" />');
              }
            }
          }
        );
      }
    );

  }
);
