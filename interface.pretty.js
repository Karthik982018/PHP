/*
* routeRequest()
* function routes the request to the appropriate function according to the method specified in the form.


*/
function routeRequest(){
    var form=document.getElementById('form1')
    var formData= new FormData(form)
    console.log(formData.get('method'))

    switch(formData.get('method')){
        case 'GET':
            getData(formData.get("resource"))
            break;
        case 'POST':
            postData(formData.get("resource"),formData.get("requestBody"))
            break;
        case 'DELETE':
            deleteData(formData.get("resource"))
            break;
        case 'PATCH':
            updateData(formData.get("resource"),formData.get("requestBody"))
            break;

    }


}


/*
* getData()
* function handles all the get requests.
* sends asynchronous request to the webservice 
* display the data in the ui calling the displayData()

* if the url is inccorect then error is printed using  displayError()
* parameter resource-is the resource entered by the user in the form


*/

function getData(resource){
    if(resource!==""){
    url='v1/'+resource
    console.log('Starting getData...');
    var request = new XMLHttpRequest()
    request.open('GET',url,true)
    request.onreadystatechange = function() {
        console.log(this.readyState + ": " + this.responseText)
        if (this.readyState == 4) {
              displayData(this.responseText,this.status);
        } }
    console.log('Sending XMLHttpRequest...');
    request.send()
    }else{
        displayError()

    }
}

/*
* postData()
* function handles all the post requests.
* sends asynchronous request to the webservice 
* display the data in the ui calling the displayData()

* if the url is inccorect then error is printed using  displayError()
* parameter data- data enetered by the user in the form
            resource-is the resource entered by the user in the form


*/

function postData(resource,data){
    if(resource!==""){
    url='v1/'+resource
    console.log(data)
    console.log('Starting getData...');
    var request = new XMLHttpRequest()
    request.open('POST',url,true)
    request.onreadystatechange = function() {
        console.log(this.readyState + ": " + this.responseText)
        if (this.readyState == 4) {
              displayData(this.responseText,this.status);
        } }
    console.log('Sending XMLHttpRequest...');
    request.send(data)
    }else{
        displayError()

    }

}
/*
* deleteData()
* function handles all the delete requests.
* sends asynchronous request to the webservice 
* display the data in the ui calling the displayData()

* if the url is inccorect then error is printed using  displayError()
* parameter resource-is the resource entered by the user in the form


*/
function deleteData(resource){
    if(resource!==""){
    url='v1/'+resource
   
    console.log('Starting getData...');
    var request = new XMLHttpRequest()
    request.open('DELETE',url,true)
    request.onreadystatechange = function() {
        console.log(this.readyState + ": " + this.responseText)
        if (this.readyState == 4) {
              displayData(this.responseText,this.status);
        } }
    console.log('Sending XMLHttpRequest...');
    request.send()
    }else{
        displayError()

    }

}

/*
* updateData()
* function handles all the update requests.
* sends asynchronous request to the webservice 
* display the data in the ui calling the displayData()

* if the url is inccorect then error is printed using  displayError()
* parameter data- data enetered by the user in the form
            resource-is the resource entered by the user in the form


*/

function updateData(resource,data){
    if(resource!==""){
    url='v1/'+resource
    console.log(data)
    console.log('Starting getData...');
    var request = new XMLHttpRequest()
    request.open('PATCH',url,true)
    request.onreadystatechange = function() {
        console.log(this.readyState + ": " + this.responseText)
        if (this.readyState == 4) {
              displayData(this.responseText,this.status);
        } }
    console.log('Sending XMLHttpRequest...');
    request.send(data)
    }else{
        displayError()

    }
}
/*
* displayData()
* function display the response to the UI
* parameter data-data in th response
            status-is the status code in the response


*/

function displayData(data,status){
    if(data!==""){
        entries = JSON.parse(data)
        console.log(status)
        var code=document.getElementById('responseCode')
        var responseBody=document.getElementById("responseBody")
        code.innerHTML=status
        responseBody.innerHTML=JSON.stringify(entries)
        var error=document.getElementById("error");
        
        error.innerHTML=""
    }
 


}
/*
* displayError()
* function display the error in the UI



*/

function displayError(){
    var error=document.getElementById("error");
    
    error.innerHTML="please fill in all the details necessary for the request"
}

/*
* clearResponse()
* function clears the response displayed in the UI.



*/

function clearResponse(){
    var code=document.getElementById('responseCode')
    var responseBody=document.getElementById("responseBody")
    code.innerHTML=""
    responseBody.innerHTML=""
    
}
