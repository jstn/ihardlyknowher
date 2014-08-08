<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--

	   ( )      / __      ___      __      ___   / //         
	  / /      //   ) ) //   ) ) //  ) ) //   ) / // //   / / 
	 / /      //   / / //   / / //      //   / / // ((___/ /  
	/ /      //   / / ((___( ( //      ((___/ / //      / /   

	   / ___       __      ___                      / __      ___      __    
	  //\ \     //   ) ) //   ) ) //  / /  / /     //   ) ) //___) ) //  ) ) 
	 //  \ \   //   / / //   / / //  / /  / /     //   / / //       //       
	//    \ \ //   / / ((___/ / ((__( (__/ /     //   / / ((____   //	

-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<script type="text/javascript">var _sf_startpt=(new Date()).getTime()</script>
		<title><?= $title ?></title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="warning" content="HC SVNT DRACONES" />
		<meta name="viewport" content="width=500;initial-scale=1" />
		<link rel="stylesheet" type="text/css" href="/reset.css" />
		<link rel="stylesheet" type="text/css" href="/ihkh.css" />
		<? foreach($css as $src): ?>
			<link rel="stylesheet" type="text/css" href="/<?= $src ?>.css" />
		<? endforeach ?>		
	</head>
	<body>
		<?= $content ?>
		<? foreach($js as $src): ?>
			<script type="text/javascript" src="/<?= $src ?>.js"></script>
		<? endforeach ?>	
	</body>
</html>