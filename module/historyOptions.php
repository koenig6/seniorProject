<form method="post" action="." id="dataEntryForm">
    <a href="/reports/index.php" id="exit"><span>x</span></a>
    <h1>Inventory History Options</h1>
    <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
    <div class="grid">
        <div class="unit whole">
            <label for="skuNumber">By SKU#:</label>
            <input type="text" name="skuNumber" placeholder="Enter SKU #" id="skuNumber" autofocus>
        </div>
        <hr>
        <div class="unit whole">
            <label for="serialNumber">By Serial#:</label>
            <input type="text" name="serialNumber" placeholder="Enter Serial #" id="serialNumber" >
        </div>
        <hr>
        <div class="unit whole">
            <label for="batchNumber">By Lot/Batch#:</label>
            <input type="text" name="batchNumber" placeholder="Enter Batch #" id="batchNumber" >
        </div>
        <button type="submit" name="action" id="action" value="history" hidden><span>i</span></button>
    </div>
</form>