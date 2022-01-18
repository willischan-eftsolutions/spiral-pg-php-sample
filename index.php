<html>
	<head>
		<script src="https://sandbox-library-checkout.spiralplatform.com/js/v2/spiralpg.min.js"></script>
		<!--
		<script src="./spiralpg.min.js"></script>
        -->
		<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
		<script type="text/javascript">
			function createOrder() {
				axios.get('./create-order.php')
					.then(function (response) {
						document.getElementById('sessionId').innerHTML = response.data;
						SpiralPG.init(response.data);
					})
					.catch(function (error) {
						console.log(error);
					});
			}
			
			function pay() {
				try {
					SpiralPG.pay();
					setTimeout("showButton();", 2000);
				} catch (err) {
					document.getElementById("loading").innerHTML = "processing...";
					setTimeout("pay();", 200);
				}
			}
			
			function showButton() {
				try {
					document.getElementById("loading").innerHTML = "Please click below button to proceed if the page is not been redirected automatically. <br>";
					document.getElementById("loading").innerHTML += "<input type=\"button\" value=\"Click here to proceed.\" onclick=\"pay();\"><br>";
				} catch (err) {
					document.getElementById("loading").innerHTML = err.message;
				}
			}
		</script>
    </head>
    <body onload="createOrder()">
	  <input type="button" value="Create Order" onclick="createOrder();" />
	  <br/>
	  <p><b>Session ID: </b><span id="sessionId" /></p>
	  <br/>
      <input type="button" value="Pay with SpiralPG" onclick="pay();" />
	  <br/>
	  <div id="loading"></div>
    </body>
</html>