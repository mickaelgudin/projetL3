<?php $this->load->view("page_template/header");
      echo form_open('/EmailController/send_mail');
?>

<div id="body" class="mbot">
	<center>
	<card class="mb-4 mt-4" title="Me contacter">
        <!--Section description-->
	    <p class="text-center w-responsive mx-auto mb-5">N'hésitez pas à me contacter, que ce soit pour une question cours, un problème du site, etc.</p>

	    <div class="row justify-content-md-center">

	        <!--Grid column-->
	        <div class="col-md-9 mb-md-0 mb-5">
	            <?php
					
					echo form_open('/EmailController/send_mail');
				?>

	                <!--Grid row-->
	                <div class="row">

	                    <!--Grid column-->
	                    <div class="col-md-6">
	                        <div class="md-form mb-2">
	                            <input type="text" id="name" name="name" class="form-control" placeholder="Votre nom (*)">
	                        </div>
	                    </div>
	                    <!--Grid column-->

	                    <!--Grid column-->
	                    <div class="col-md-6">
	                        <div class="md-form mb-4">
	                            <input type="text" id="email" name="email" class="form-control" placeholder="Votre email (*)">
	                        </div>
	                    </div>
	                    <!--Grid column-->

	                </div>
	                <!--Grid row-->
	                <!--Grid row-->
	                <div class="row">
	                    <div class="col-md-12">
	                        <div class="md-form mb-4">
	                            <input type="text" id="subject" name="subject" class="form-control" placeholder="Titre (*)">
	                        </div>
	                    </div>
	                </div>
	                <!--Grid row-->

	                <!--Grid row-->
	                <div class="row">

	                    <!--Grid column-->
	                    <div class="col-md-12">

	                        <div class="md-form mb-4">
	                            <textarea type="text" id="message" name="message" rows="2" class="form-control md-textarea" placeholder="Votre message (*)"></textarea>
	                        </div>

	                    </div>
	                </div>
	                <!--Grid row-->

	            <center>
		            <div>
		                <input class="btn btn-primary" type = "submit" value = "Envoyer le mail">
		            </div>
	            </center>
	            <div class="status"></div>
	        </div>
	        <!--Grid column-->
	    </div>

	</card>
	</center>
</div>

<?php
echo form_close();
?>

<?php $this->load->view("page_template/footer");?>

<script>
Vue.use(VueToast);
if("<?=$this->session->flashdata('email_sent')?>" === "Veuillez vérifier la saisie des champs."){
    Vue.$toast.error("<?=$this->session->flashdata('email_sent');?>", {
	  position: 'top', 
	  duration: 8000
	})
}
else if("<?=$this->session->flashdata('email_sent')?>" === "Votre message a bien été envoyé."){
	Vue.$toast.success("<?=$this->session->flashdata('email_sent');?>", {
	  position: 'top',
	  duration: 8000
	})
}
</script>

</body>
</html>

