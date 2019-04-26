<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('fitbit');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
 <div class="col-xs-12 eqLogicThumbnailDisplay">
  <legend>{{Mes équipements Fitbit}}
  </legend>
  <div class="eqLogicThumbnailContainer">
    <div class="cursor eqLogicAction logoPrimary" data-action="add">
      <i class="fa fa-plus-circle"></i>
    <br/>
    <span>{{Ajouter une personne}}</span>
  </div>
	  
	  	</div>
		<legend><i class="fas fa-table"></i> {{Mes équipement Fitbit}}</legend>
	  <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
	 <div class="eqLogicThumbnailContainer">
  <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '" >';
	echo '<img src="' . $plugin->getPathImgIcon() . '" />';
	echo '<br/>';
	echo '<span>' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-xs-12 eqLogic" style="display: none;">
	<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
  </ul>
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
      <form class="form-horizontal">
        <fieldset>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom de la personne}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de la personne}}"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
            <div class="col-sm-3">
              <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                <option value="">{{Aucun}}</option>
                <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
             </select>
           </div>
         </div>
         <div class="form-group">
          <label class="col-sm-3 control-label"></label>
          <div class="col-sm-9">
            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">{{URL de retour}}</label>
          <div class="col-lg-9">
            <span><?php echo network::getNetworkAccess('external') . '/plugins/fitbit/core/php/callback.php?apikey=' . jeedom::getApiKey('fitbit') . '&eqLogic_id='; ?><span class="span_fitbitCallbackId"></span></span>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Client ID}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="client_id"/>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Secret key}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="client_secret"/>
          </div>
        </div>
          <div class="form-group">
          <label class="col-lg-3 control-label">{{URL de subscription}}</label>
          <div class="col-lg-9">
            <span><?php echo network::getNetworkAccess('external') . '/plugins/fitbit/core/php/callback.php?update=1&apikey=' . jeedom::getApiKey('fitbit') . '&eqLogic_id='; ?><span class="span_fitbitCallbackId"></span></span>
          </div>
        </div>
         <div class="form-group">
          <label class="col-sm-3 control-label">{{Subscription id}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="subscription_id"/>
          </div>
        </div>
         <div class="form-group">
          <label class="col-sm-3 control-label">{{Subscription code}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="subscription_code"/>
          </div>
        </div>
        <div class="form-group">
          <label class="col-lg-3 control-label">{{Lier}}</label>
          <div class="col-lg-2">
            <a class="btn btn-default" id="bt_linkToUser"><i class='fa fa-refresh'></i> {{Lier à un utilisateur}}</a>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <div role="tabpanel" class="tab-pane" id="commandtab">
    <br/>
    <table id="table_cmd" class="table table-bordered table-condensed">
      <thead>
        <tr>
          <th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>


</div>
</div>

<?php include_file('desktop', 'fitbit', 'js', 'fitbit');?>
<?php include_file('core', 'plugin.template', 'js');?>
