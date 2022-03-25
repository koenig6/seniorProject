<h1>Complete Work Order</h1>
<div class="completeWOModal">
    <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
    <input type="text" name="workOrderNumber" id="woNumInput" placeholder="Enter Work Order #" id="workOrderNumber" required autofocus>
</div>
<button class="bs-btn btn-blue" onclick="completeWOModal()"><i class="bi bi-arrow-bar-up"></i></button>
