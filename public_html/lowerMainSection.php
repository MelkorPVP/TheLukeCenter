<div class="row g-4 align-items-stretch mt-3">	
	<div class="col-12 col-md-7">				
		<div class="text-left">				
			<!--					
				<h3 class="h5 text-brand mb-1">The Pacific Program</h3>					
				<p class="mb-1 fw-semibold text-secondary">The Oregon Gardens</p>					
				<p class="mb-3">October 12th - 16th</p>					
				<a class="btn btn-brand" href="/pacificProgram.php">Learn More</a>					
			-->				
			<h3 class="h5 text-brand mb-1"><?= htmlspecialchars($programName, ENT_QUOTES) ?></h3>				
			<p class="mb-1 fw-semibold text-secondary"><?= htmlspecialchars($programLocation, ENT_QUOTES) ?></p>				
			<p class="mb-3"><?= htmlspecialchars($programDates, ENT_QUOTES) ?></p>				
			<a class="btn btn-brand" href="/pacificProgram.php">Learn More</a>				
		</div>				
	</div>	
	<div class="col-12 col-md-5 d-flex">					
		<div class="text-left">				
			<h4 class="h5 text-brand mb-1">About Catalytic Leadership</h4>				
			<p class="mb-3">Explore the principles of Catalytic Leadership that drive collaborative problem solving and real-world impact across sectors.</p>								
			<a class="btn btn-outline-brand" href="/catalyticLeadership.php">Explore the Framework</a>									
		</div>					
	</div>			
	<div class="mt-4">
		<h2 class="h5 text-brand mb-1">Comments About The Pacific Program</h2>
		<div id="testimonialRotator" class="lc-testimonial fs-6" aria-live="polite" data-testimonial-endpoint="/handleTestimonialData.php"></div>
	</div>	
</div>    