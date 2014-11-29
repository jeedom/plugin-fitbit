<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
        <?php
        if (!class_exists('OAuth')) {
            echo '<div class="alert alert-danger">';
            echo 'Classe OAuth non trouvée merci de l\'installer : "sudo apt-get install php5-oauth; sudo service php5-fpm restart"';
            echo '</div>';
        } else {
            if (!fitbit::testFitbitConnexion()) {
                echo '<div class="alert alert-danger">';
                echo 'Aucune connexion valide aux serveurs Fitbit';
                echo '</div>';
            } else {
                echo '<div class="alert alert-success">';
                echo 'Connexion aux serveur Fitbit OK';
                echo '</div>';
            }
            ?>
            <div class="form-group">
                <label class="col-lg-4 control-label">URL de retour</label>
                <div class="col-lg-4">
                    <span><?php echo fitbit::getCallbackUri(); ?></span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-4 control-label">Synchroniser</label>
                <div class="col-lg-2">
                    <a class="btn btn-default" id="bt_syncWithFitbit"><i class='fa fa-refresh'></i> Synchroniser mes équipements</a> 
                </div>
            </div>
        <?php } ?>
    </fieldset>
</form>

<script>
    $('#bt_syncWithFitbit').on('click', function () {
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/fitbit/core/ajax/fitbit.ajax.php", // url du fichier php
            data: {
                action: "syncWithFitbit",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                $('#div_alert').showAlert({message: 'Synchronisation réussie', level: 'success'});
            }
        });
    });
</script>
