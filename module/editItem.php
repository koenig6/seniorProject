<form method="post" action="." id="dataEntryForm">
    <h1>Edit Item Details</h1>
    <?php if($message){echo'<h2>' . $message . '</h2>';}?>
    <ul>
            
        <div class="grid">
        <div class="unit half">
        <li>
            <label for="skuNumber">Sku Number:</label>
            <input type="text" name="skuNumber" id="skuNumber" value="<?php echo $itemDetails['skuNumber'] ?>" readonly="readonly">
        </li>
        <li>
            <label for="paperColor">Label Color:</label>
            <select name="paperColor" required>
                <option value="" disabled selected>Select Color</option>
                <?php
                    for($i = 0; $i < sizeof($paperColor); $i++){
                        echo '<option value=' . $paperColor[$i][0];
                                if($itemDetails['paperColorID'] == $paperColor[$i][0]){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $paperColor[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="palletQty">Pallet Qty:</label>
            <input type="number" name="palletQty" placeholder="Enter Pallet Quantity" 
                   id="palletQty" value="<?php echo $itemDetails['palletQty'] ?>">
        </li>
        <div class="checkBox">
            <li>
                <label for="kit">Kit:</label>
                <input type="checkbox" name="kit" value="1" id="kit" <?php echo ($itemDetails['kit'] == 1 ? 'checked' : 0)?>>
            </li>
            <li>
                <label for="component">Component:</label>
                <input type="checkbox" name="component" id="component" <?php echo ($itemDetails['component'] == 1 ? 'checked' : 0)?>>
            </li>
        </div>
        <li>
            <label for="timeStudy">Time Study:</label>
            <input type="text" name="timeStudy" placeholder="00:00:00 (h:m:s)" 
                   id="timeStudy" value="<?php echo $itemDetails['timeStudy'] ?>">
        </li>
        </div>
        <div class="unit half">
            <div class="checkBox">
                <li>
                    <label for="billablePallet">BillablePallet:</label>
                    <input type="checkbox" name="billablePallet" id="billablePallet" <?php echo ($itemDetails['billablePallet'] == 1 ? 'checked' : 0)?>>
                </li>
            </div>
        <li>
            <label for="rackCharge">Rack Charge:</label>
            <input type="number" step="any" name="rackCharge" placeholder="Enter Rack Charge" 
                   id="rackCharge" value="<?php echo $itemDetails['rackCharge'] ?>" >
        </li>
        <li>
            <label for="bulkCharge">Bulk Charge:</label>
            <input type="number"  step="any" name="bulkCharge" placeholder="Enter Bulk Charge" 
                   id="bulkCharge" value="<?php echo $itemDetails['bulkCharge'] ?>">
        </li>
        <li>
            <label for="caseCharge">Case Charge:</label>
            <input type="number"  step="any" name="caseCharge" placeholder="Enter Case Charge" 
                   id="caseCharge" value="<?php echo $itemDetails['caseCharge'] ?>">
        </li>
            <div class="checkBox">
                <li>
                    <label for="poRequired">PO Required:</label>
                    <input type="checkbox" name="poRequired" id="poRequired" <?php echo ($itemDetails['poRequired'] == 1 ? 'checked' : 0)?>>
                </li>
                <li>
                    <label for="batchRequired">Batch Required:</label>
                    <input type="checkbox" name="batchRequired" id="batchRequired" <?php echo ($itemDetails['batchRequired'] == 1 ? 'checked' : 0)?>>
                </li>
                <li>
                    <label for="palletNumberRequired">Pallet# Required:</label>
                    <input type="checkbox" name="palletNumberRequired" id="palletNumberRequired" <?php echo ($itemDetails['palletNumberRequired'] == 1 ? 'checked' : 0)?>>
                </li>
            </div>
        </div>
        </div>
    </ul>
    <button type="submit" name="action" id="action" value="updateItem"><span>i</span></button>
</form>

