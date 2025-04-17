console.log('Checking updateCallback type before check:', typeof updateCallback, updateCallback);
if (typeof updateCallback === 'function') {
    console.log("Calling update callback after saving setting."); 
    updateCallback();
} else {
     console.warn("Update callback was not provided or is not a function in saveResponsiveSetting.");
} 