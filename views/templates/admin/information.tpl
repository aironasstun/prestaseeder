<div class="panel col-lg-12">
	<div class="panel-heading">Information</div>
	<div class="well clearfix">
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