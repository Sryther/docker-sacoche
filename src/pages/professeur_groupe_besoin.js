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
    $('#table_action').tablesorter({ headers:{2:{sorter:false},3:{sorter:false},4:{sorter:false}} });
    var tableau_tri = function(){ $('#table_action').trigger( 'sorton' , [ [[0,0],[1,0]] ] ); };
    var tableau_maj = function(){ $('#table_action').trigger( 'update' , [ true ] ); };
    tableau_tri();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , niveau_nom , nom , eleve_nombre , eleve_liste , prof_nombre , prof_liste )
    {
      $('#f_action').val(mode);
      $('#f_id').val(id);
      $('#f_niveau').html(select_niveau.replace('>'+niveau_nom,' selected>'+niveau_nom));
      $('#f_nom').val(nom);
      $('#f_eleve_nombre').val(eleve_nombre);
      $('#f_eleve_liste').val(eleve_liste);
      $('#f_prof_nombre').val(prof_nombre);
      $('#f_prof_liste').val(prof_liste);
      // pour finir
      $('#form_gestion h2').html(mode[0].toUpperCase() + mode.substring(1) + " un groupe de besoin");
      if(mode!='supprimer')
      {
        $('#gestion_edit').show(0);
        $('#gestion_delete').hide(0);
      }
      else
      {
        $('#gestion_delete_identite').html(nom);
        $('#gestion_edit').hide(0);
        $('#gestion_delete').show(0);
      }
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
      if(mode=='ajouter') { $('#f_nom').focus(); }
    }

    /**
     * Ajouter un groupe de besoin : mise en place du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      // Report des valeurs transmises via un formulaire depuis un tableau de synthèse bilan
      if(reception_todo)
      {
        reception_todo = false;
      }
      else
      {
        reception_users_texte  = 'aucun';
        reception_users_liste  = '';
      }
      // Afficher le formulaire
      afficher_form_gestion( mode , '' /*id*/ , '' /*niveau_nom*/ , '' /*nom*/ , reception_users_texte /*eleve_nombre*/ , reception_users_liste /*eleve_liste*/ , 'moi seul' /*prof_nombre*/ , '' /*prof_liste*/ );
    };

    /**
     * Modifier un groupe de besoin : mise en place du formulaire
     * @return void
     */
    var modifier = function()
    {
      mode = $(this).attr('class');
      var objet_tr     = $(this).parent().parent();
      var objet_tds    = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tr.attr('id').substring(3);
      var niveau_nom   = objet_tds.eq(0).html();
      var nom          = objet_tds.eq(1).html();
      var eleve_nombre = objet_tds.eq(2).html();
      var prof_nombre  = objet_tds.eq(3).html();
      // enlever l'ordre du niveau caché
      niveau_nom = niveau_nom.substring(niveau_ordre_longueur+7,niveau_nom.length); // <i>...</i>
      // liste des élèves et des profs
      var eleve_liste   = tab_eleves[id];
      var prof_liste    = tab_profs[id];
      // Afficher le formulaire
      afficher_form_gestion( mode , id , niveau_nom /* volontairement sans unescapeHtml() */ , unescapeHtml(nom) , eleve_nombre , eleve_liste , prof_nombre , prof_liste );
    };

    /**
     * Retirer un groupe de besoin : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr     = $(this).parent().parent();
      var objet_tds    = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id           = objet_tr.attr('id').substring(3);
      var nom          = objet_tds.eq(1).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , '' /*niveau_nom*/ , unescapeHtml(nom) , '' /*eleve_nombre*/ , '' /*eleve_liste*/ , '' /*prof_nombre*/ , '' /*prof_liste*/ );
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
     * Choisir les élèves associés à un groupe : mise en place du formulaire
     * @return void
     */
    var choisir_eleve = function()
    {
      // Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
      $('#zone_eleve li.li_m1 span.gradient_pourcent').html('');
      cocher_eleves( $('#f_eleve_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_eleve' , onStart:function(){$('#zone_eleve').css("display","block");} , onClosed:function(){$('#zone_eleve').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

    /**
     * Choisir les professeurs associés à un groupe : mise en place du formulaire
     * @return void
     */
    var choisir_prof = function()
    {
      cocher_profs( $('#f_prof_liste').val() );
      // Afficher la zone
      $.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
      $(document).tooltip("destroy");infobulle(); // Sinon, bug avec l'infobulle contenu dans le fancybox qui ne disparait pas au clic...
    };

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#table_action').on( 'click' , 'q.ajouter'   , ajouter );
    $('#table_action').on( 'click' , 'q.modifier'  , modifier );
    $('#table_action').on( 'click' , 'q.supprimer' , supprimer );

    $('#form_gestion').on( 'click' , '#bouton_annuler' , annuler );
    $('#form_gestion').on( 'click' , '#bouton_valider' , function(){formulaire.submit();} );
    $('#form_gestion').on( 'keyup' , 'input,select'    , function(e){intercepter(e);} );

    $('#form_gestion').on( 'click' , 'q.choisir_eleve' , choisir_eleve );
    $('#form_gestion').on( 'click' , 'q.choisir_prof'  , choisir_prof );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Cocher / décocher par lot des individus
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#prof_check_all').click
    (
      function()
      {
        $('.prof_liste').find('input:enabled').prop('checked',true);
        return false;
      }
    );
    $('#prof_uncheck_all').click
    (
      function()
      {
        $('.prof_liste').find('input:enabled').prop('checked',false);
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer le cadre des élèves associés à un groupe (annuler / retour)
// Clic sur le bouton pour fermer le cadre des professeurs associés à un groupe (annuler / retour)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#annuler_eleve , #annuler_profs').click
    (
      function()
      {
        $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
        return false;
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour valider le choix des élèves associés à un groupe
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
// Clic sur le bouton pour valider le choix des profs associés à un groupe
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#valider_profs').click
    (
      function()
      {
        var liste = '';
        var nombre = 0;
        $("#zone_profs input[type=checkbox]:checked").each
        (
          function()
          {
            liste += $(this).val()+'_';
            nombre++;
          }
        );
        liste  = (nombre==1) ? '' : liste.substring(0,liste.length-1) ;
        nombre = (nombre==1) ? 'moi seul' : nombre+' profs' ;
        $('#f_prof_liste').val(liste);
        $('#f_prof_nombre').val(nombre);
        $('#annuler_profs').click();
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
          f_niveau      : { required:true },
          f_nom         : { required:true , maxlength:20 },
          f_eleve_liste : { required:true },
          f_prof_liste  : { required:false }
        },
        messages :
        {
          f_niveau      : { required:"niveau manquant" },
          f_nom         : { required:"nom manquant" , maxlength:"20 caractères maximum" },
          f_eleve_liste : { required:"élève(s) manquant(s)" },
          f_prof_liste  : { }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          element.after(error);
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
        switch (mode)
        {
          case 'ajouter':
            $('#table_action tbody tr.vide').remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
            var niveau_nom = $('#f_niveau option:selected').text();
            var new_tr = responseJSON['html'].replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
            $('#table_action tbody').prepend(new_tr);
            eval( responseJSON['script'] );
            break;
          case 'modifier':
            var niveau_nom = $('#f_niveau option:selected').text();
            var new_tds = responseJSON['html'].replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
            $('#id_'+$('#f_id').val()).addClass("new").html(new_tds);
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

    // Initialiser l'affichage au démarrage
    if( reception_todo )
    {
      $('q.ajouter').click();
    }

  }
);
