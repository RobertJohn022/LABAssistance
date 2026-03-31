document.addEventListener('DOMContentLoaded', function() {
    
    // Form elements
    const checkWash = document.getElementById('checkWash');
    const checkDry = document.getElementById('checkDry');
    const checkFold = document.getElementById('checkFold');
    const foldContainer = document.getElementById('foldContainer'); // The label wrapping the fold checkbox

    // Add-ons
    const suppliesSection = document.getElementById('suppliesSection'); 
    const supplyDetergent = document.getElementById('supplyDetergent');
    const supplySoftener = document.getElementById('supplySoftener');
    const suppliesHeader = document.getElementById('suppliesHeader');

    // Quantity inputs
    const standardInputs = document.getElementById('standardInputs');
    const foldOnlyInput = document.getElementById('foldOnlyInput');
    const qtyColored = document.getElementById('qtyColored');
    const qtyWhite = document.getElementById('qtyWhite');
    const qtyFold = document.getElementById('qtyFold');

    // UI controls
    const btnPlaceOrder = document.getElementById('btnPlaceOrder');
    const totalPriceDisplay = document.getElementById('totalPrice');

    const elements = [checkWash, checkDry, checkFold, supplyDetergent, supplySoftener, qtyColored, qtyWhite, qtyFold];
    elements.forEach(el => {
        if(el) {
            el.addEventListener('change', updateOrderState);
            el.addEventListener('input', updateOrderState);
        }
    });

    function updateOrderState() {
        const isWash = checkWash.checked;
        const isDry = checkDry.checked;

        // --- NEW FOLD LOGIC ---
        // If Wash is checked but Dry is not, disable the Fold option
        if (isWash && !isDry) {
            checkFold.disabled = true;
            checkFold.checked = false; // Force it to uncheck
            foldContainer.classList.add('opacity-50');
            foldContainer.style.pointerEvents = 'none';
        } else {
            checkFold.disabled = false;
            foldContainer.classList.remove('opacity-50');
            foldContainer.style.pointerEvents = 'auto';
        }

        // Re-evaluate isFold after the above logic might have unchecked it
        const isFold = checkFold.checked;
        // ----------------------

        // Toggle supplies
        if (isWash) {
            supplyDetergent.disabled = false;
            supplySoftener.disabled = false;
            
            // Enable clicks
            if (suppliesSection) {
                suppliesSection.style.pointerEvents = 'auto';
                suppliesSection.classList.remove('opacity-50');
            }

            if (suppliesHeader) {
                suppliesHeader.classList.remove('text-muted');
                suppliesHeader.classList.add('text-dark');
            }
        } else {
            supplyDetergent.disabled = true;
            supplySoftener.disabled = true;
            supplyDetergent.checked = false;
            supplySoftener.checked = false;

            // Disable clicks
            if (suppliesSection) {
                suppliesSection.style.pointerEvents = 'none';
                suppliesSection.classList.add('opacity-50');
            }

            if (suppliesHeader) {
                suppliesHeader.classList.add('text-muted');
                suppliesHeader.classList.remove('text-dark');
            }
        }

        // Toggle input modes
        const isFoldOnly = isFold && !isWash && !isDry;

        if (isFoldOnly) {
            standardInputs.classList.add('d-none');
            foldOnlyInput.classList.remove('d-none');
        } else {
            standardInputs.classList.remove('d-none');
            foldOnlyInput.classList.add('d-none');
        }

        // Calc total
        let totalLoadCount = 0;
        
        if (isFoldOnly) {
            totalLoadCount = parseInt(qtyFold.value) || 0;
        } else {
            totalLoadCount = (parseInt(qtyColored.value) || 0) + (parseInt(qtyWhite.value) || 0);
        }

        let costPerLoad = 0;
        if (isWash) costPerLoad += SERVICE_PRICES.wash; 
        if (isDry) costPerLoad += SERVICE_PRICES.dry;
        if (isFold) costPerLoad += SERVICE_PRICES.fold; 
        
        if (isWash) {
            if (supplyDetergent.checked) costPerLoad += SERVICE_PRICES.detergent;
            if (supplySoftener.checked) costPerLoad += SERVICE_PRICES.softener;
        }

        const grandTotal = totalLoadCount * costPerLoad;
        totalPriceDisplay.innerText = "₱" + grandTotal.toFixed(2);

        // Update button
        if (grandTotal === 0 || (!isWash && !isDry && !isFold)) {
            btnPlaceOrder.disabled = true;
        } else {
            btnPlaceOrder.disabled = false;
        }
    }

    updateOrderState();
});