<?php $this->load->view("page_template/header"); 

$email = '';
if($_SESSION['user'] === 'etudiant')
    $email = $this->encrypt->decode(get_cookie('ux_e189CDS8CSDC98JCPDSCDSCDSCDSD8C9SD'));
else if($_SESSION['user'] === 'admin')
    $email = $this->encrypt->decode(get_cookie('ux_ad_189CDS8CSDC98JCPDSCDSCDSCDSD8C9SD'));
    
?>
<div id="body" class="mbot">
<card class="mb-4" >
   
		<?php echo form_open('/CompteController/hasChange');?>
		
		<center>
    	<h5 class="card-title">Mon compte</h5>
		<div class="justify-content-md-center">
            <div class="col-md-4 form-group mb-2">
        		<label style="font-weight:bold">Email</label> 
        		<input type="email" class="form-control"name="email" value=<?=$email?>><br>
        		<label style="font-weight:bold">Saisissez votre mot de passe actuel</label> 
        		<input type="password" class="form-control"name="oldpassword"><br>
        		<label style="font-weight:bold">Saisissez votre nouveau mot de passe</label> 
        		<input type="password" class="form-control"name="newpassword"><br>
        		<label style="font-weight:bold">Resaisissez votre nouveau mot de passe</label> 
        		<input type="password" class="form-control"name="checknewpassword"><br>
        		
        		<input class="btn btn-primary" type="submit" name="modifier" value="Modifier" /><br><br>
			</div>
		</div>
		
		

		
		<?php echo form_close();?>
		</center>
</card>
</div>

</body>
<?php $this->load->view("page_template/footer");?>

<script>
Vue.use(VueToast);
if("<?=$_SESSION['retour_modification']?>" === "Le mot de passe a été changé"){
	Vue.$toast.success("<?=$_SESSION['retour_modification'];?>", {
	  position: 'top',
	  duration: 8000
	})
}	
else if("<?=$_SESSION['retour_modification']?>" === "Vérifiez l'email ou le mot de passe" || "<?=$_SESSION['retour_modification']?>" === "Veuillez saisir le même mot de passe"){
    Vue.$toast.error("<?=$_SESSION['retour_modification'];?>", {
	  position: 'top', 
	  duration: 8000
	})
}
<?php unset($_SESSION['retour_modification']);?>
</script>
</html>