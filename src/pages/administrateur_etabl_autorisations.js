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

    var tab_restriction_type_to_tab = new Array();
    tab_restriction_type_to_tab['ONLY_PP']    = tab_profil_join_groupes;
    tab_restriction_type_to_tab['ONLY_COORD'] = tab_profil_join_matieres;

    /*
     * Afficher ou masquer des éléments de formulaire
     */
    function view_bilans()
    {
      // "droit_releve_etat_acquisition" => "droit_releve_moyenne_score" + "droit_releve_pourcentage_acquis"
      var opacite_parent = ( $('#form_autorisations input[name="droit_releve_etat_acquisition"][value="TUT"]').is(':checked') ) ? 1 : 0 ;
      var opacite_eleve  = ( $('#form_autorisations input[name="droit_releve_etat_acquisition"][value="ELV"]').is(':checked') ) ? 1 : 0 ;
      var opacite_ligne  = ( opacite_parent || opacite_eleve ) ? 1 : 0 ;
      $('#tr_droit_releve_moyenne_score , #tr_droit_releve_pourcentage_acquis').fadeTo(0,opacite_ligne);
      $('#form_autorisations input[name="droit_releve_moyenne_score"][value="TUT"] , #form_autorisations input[name="droit_releve_pourcentage_acquis"][value="TUT"]').parent().fadeTo(0,opacite_parent);
      $('#form_autorisations input[name="droit_releve_moyenne_score"][value="ELV"] , #form_autorisations input[name="droit_releve_pourcentage_acquis"][value="ELV"]').parent().fadeTo(0,opacite_eleve);
      // "droit_releve_etat_acquisition" + "droit_releve_moyenne_score" + "droit_releve_pourcentage_acquis" => droit_releve_conversion_sur_20
      var opacite_parent = ( opacite_parent && ( $('#form_autorisations input[name="droit_releve_moyenne_score"][value="TUT"]').is(':checked') || $('#form_autorisations input[name="droit_releve_pourcentage_acquis"][value="TUT"]').is(':checked') ) ) ? 1 : 0 ;
      var opacite_eleve  = ( opacite_eleve  && ( $('#form_autorisations input[name="droit_releve_moyenne_score"][value="ELV"]').is(':checked') || $('#form_autorisations input[name="droit_releve_pourcentage_acquis"][value="ELV"]').is(':checked') ) ) ? 1 : 0 ;
      var opacite_ligne  = ( opacite_ligne  && ( opacite_parent || opacite_eleve ) ) ? 1 : 0 ;
      $('#form_autorisations input[name="droit_releve_conversion_sur_20"][value="TUT"]').parent().fadeTo(0,opacite_parent);
      $('#form_autorisations input[name="droit_releve_conversion_sur_20"][value="ELV"]').parent().fadeTo(0,opacite_eleve);
      $('#tr_droit_releve_conversion_sur_20').fadeTo(0,opacite_ligne);
    }
    view_bilans();

    /*
     * Afficher ou masquer des éléments de formulaire
     */
    function view_socle()
    {
      var opacite_parent = $('#form_autorisations input[name="droit_socle_acces"][value="TUT"]').is(':checked') ? 1 : 0 ;
      var opacite_eleve  = $('#form_autorisations input[name="droit_socle_acces"][value="ELV"]').is(':checked') ? 1 : 0 ;
      var opacite_ligne  = ( opacite_parent || opacite_eleve ) ? 1 : 0 ;
      $('#form_autorisations input[name="droit_socle_proposition_positionnement"][value="TUT"]').parent().fadeTo(0,opacite_parent);
      $('#form_autorisations input[name="droit_socle_proposition_positionnement"][value="ELV"]').parent().fadeTo(0,opacite_eleve);
      $('#tr_droit_socle_proposition_positionnement').fadeTo(0,opacite_ligne);
    }
    view_socle();

    /*
     * Initialisation au chargement de l'opacité des cases dépendant d'un type de restriction, pour tout le document
     */
    function view_all_pp_coord()
    {
      for(var restriction_type in tab_restriction_type_to_tab) // Parcourir un tableau associatif...
      {
        $('#form_autorisations input[value="'+restriction_type+'"]').each
        (
          function()
          {
            var objet = $(this).attr('name');
            var count_check = 0;
            for(var value in tab_restriction_type_to_tab[restriction_type]) // Parcourir un tableau associatif...
            {
              if(tab_restriction_type_to_tab[restriction_type][value])
              {
                count_check += $('#form_autorisations input[name="'+objet+'"][value="'+value+'"]').is(':checked') ? 1 : 0 ;
              }
            }
            var opacite = count_check ? 1 : 0 ;
            $(this).parent().fadeTo(0,opacite);
          }
        );
      }
    }
    view_all_pp_coord();

    /*
     * Mise à jour de l'opacité des cases dépendant d'un type de restriction, pour un droit donné
     */
    function view_pp_coord(objet)
    {
      for(var restriction_type in tab_restriction_type_to_tab) // Parcourir un tableau associatif...
      {
        if($('#form_autorisations input[name="'+objet+'"][value="'+restriction_type+'"]').length)
        {
          var count_check = 0;
          for(var value in tab_restriction_type_to_tab[restriction_type]) // Parcourir un tableau associatif...
          {
            if(tab_restriction_type_to_tab[restriction_type][value])
            {
              count_check += $('#form_autorisations input[name="'+objet+'"][value="'+value+'"]').is(':checked') ? 1 : 0 ;
            }
          }
          var opacite = count_check ? 1 : 0 ;
          $('#form_autorisations input[name="'+objet+'"][value="'+restriction_type+'"]').parent().fadeTo(0,opacite);
        }
      }
    }

    /*
     * Mise à jour de la couleur de fond des cases dépendant d'un type de restriction, pour un droit donné
     */
    function coloriser_cellules_ligne(objet)
    {
      var check_pp    = $('#form_autorisations input[name="'+objet+'"][value="ONLY_PP"]'   ).is(':checked') ? true : false ;
      var check_coord = $('#form_autorisations input[name="'+objet+'"][value="ONLY_COORD"]').is(':checked') ? true : false ;
      $('#form_autorisations input[name="'+objet+'"]').each
      (
        function()
        {
          var valeur = $(this).val();
          var color = ($(this).is(':checked')) ? ( ( (check_pp && tab_profil_join_groupes[valeur]) || (check_coord && tab_profil_join_matieres[valeur]) ) ? 'bj' : 'bv' ) : 'br' ;
          $(this).parent().attr('class','hc '+color);
        }
      );
    }

    /*
     * Actualiser des affichage ou des couleurs
     */
    function actualiser_si_besoin(objet)
    {
      if( (objet=='droit_releve_etat_acquisition') || (objet=='droit_releve_moyenne_score') || (objet=='droit_releve_pourcentage_acquis') )
      {
        view_bilans();
      }
      if(objet=='droit_socle_acces')
      {
        view_socle();
      }
      view_pp_coord(objet);
      coloriser_cellules_ligne(objet);
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Alerter sur la nécessité de valider
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#form_autorisations input").change
    (
      function()
      {
        var objet  = $(this).attr('name');
        actualiser_si_besoin(objet);
        $('#ajax_msg_'+objet).attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialiser un formulaire avec les valeurs par défaut
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_autorisations button[name=initialiser]').click
    (
      function()
      {
        var objet = $(this).parent().parent().attr('id').substring(3);
        for(var value in tab_init[objet]) // Parcourir un tableau associatif...
        {
          $('#form_autorisations input[name="'+objet+'"][value="'+value+'"]').prop('checked',tab_init[objet][value]);
        }
        actualiser_si_besoin(objet);
        $('#ajax_msg_'+objet).attr('class','alerte').html("Enregistrer pour confirmer.");
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Soumission du formulaire
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_autorisations button[name=valider]').click
    (
      function()
      {
        var obj_bouton = $(this);
        var objet = obj_bouton.parent().parent().attr('id').substring(3);
        var tab_check = new Array(); $('#form_autorisations input[name='+objet+']:checked').each(function(){tab_check.push($(this).val());});
        obj_bouton.prop('disabled',true);
        $('#ajax_msg_'+objet).attr('class','loader').html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_objet='+objet+'&f_profils='+tab_check,
            dataType : 'json',
            error : function(jqXHR, textStatus, errorThrown)
            {
              obj_bouton.prop('disabled',false);
              $('#ajax_msg_'+objet).attr('class','alerte').html(afficher_json_message_erreur(jqXHR,textStatus));
              return false;
            },
            success : function(responseJSON)
            {
              initialiser_compteur();
              obj_bouton.prop('disabled',false);
              if(responseJSON['statut']==true)
              {
                $('#ajax_msg_'+objet).attr('class','valide').html("Droits enregistrés !");
              }
              else
              {
                $('#ajax_msg_'+objet).attr('class','alerte').html(responseJSON['value']);
              }
            }
          }
        );
      }
    );

  }
);
