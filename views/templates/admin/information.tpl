<div class="panel col-lg-12">
	<div class="panel-heading">Information</div>
    <div class="well clearfix">
        <h3>Product cronform</h3>
        <div class="col-md-6">
            <form action="{$cronPath}" method="get">
                <div class="form-group">
                    <label for="token">Token: </label>
                    <input class="form-control" type="text" name="token" id="token" value="{Tools::encrypt('prestaseeder')}" readonly>
                </div>
                <div class="form-group">
                    <label for="action">Select an action: </label>
                        <select class="form-control" name="action" id="action">
                            <option value="createProducts">Create products</option>
                            <option value="createAttributeGroups">Create attribute groups</option>
                            <option value="createAttributes">Create attributes</option>
                            <option value="createFeatures">Create features</option>
                            <option value="createFeatureValues">Create feature values</option>
                            <option value="createCategories">Create categories</option>
                            <option value="assignToCategories">Assign to categories</option>
                            <option value="createCombinations">Create combinations</option>
                            <option value="assignFeaturesToProducts">Assign features</option>
                            <option value="full">Full cron (use 5 as example (products are x*5))</option>
                        </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount: </label>
                    <input class="form-control" type="text" name="amount" id="amount" value="">
                </div>
                    <input type="submit" class="btn btn-danger" value="Seed it!">
            </form>
        </div>

    </div>
	<div class="well clearfix col-md-12">
		<h3>Run as a cron</h3>
		<p>This module is based on cronjobs.</p>
        <p>Currently, you can enter your custom image api in settings page.</p>
        <p>Cron currently takes 3 parameters</p>
        <ul>
            <li>token</li>
            <li>action</li>
            <li>amount</li>
        </ul>
		<p>/modules/prestaseeder/prestaseeder.cron.php?token={Tools::encrypt('prestaseeder')}&action=createProducts&amount=10</p>
	</div>
</div>