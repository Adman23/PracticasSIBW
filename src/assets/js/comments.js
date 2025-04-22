
// Event listeners para ejecutar las funciones cuando se escribe en el formulario
let fname = document.getElementById("fname");
fname.addEventListener("keyup", function() {fname.value=checkBadWords(fname.value) })

let femail = document.getElementById("femail");
femail.addEventListener("keyup", function() {femail.value=checkBadWords(femail.value) })

let fcontent = document.getElementById("fcontent");
fcontent.addEventListener("keyup", function() {fcontent.value=checkBadWords(fcontent.value) })



// Definiciones-----------------------------------------------------------------------------------
let menu = document.getElementById("comment-menu");
let prohibited_words_json =  menu.getAttribute("data-prohibitedWords");
const prohibited_words = JSON.parse(prohibited_words_json);

// Funciones--------------------------------------------------------------------------------------
function checkBadWords(text)
{   
    for (pw in prohibited_words)
        if(text.includes(prohibited_words[pw])){
            text = text.replace(new RegExp(prohibited_words[pw], 'i'), '*'.repeat(prohibited_words[pw].length));
        }
    return text;
}

function showMenu()
{
    menu.classList.toggle("active-comments-menu");
}

// Función para validar el comentario (Se ejecuta antes de postComment)
function validateComment(event)
{
    let errorM = document.getElementById("error-message");
    errorM.textContent = '';
    const emailReg = new RegExp('^[a-zA-Z0-9_%+-]+@([a-zA-Z0-9-]+\.)?[a-zA-Z0-9]+\.[a-zA-Z]{2,}$');
    try{
        const form = event.target;
        let name = form.fname.value;
        let email = form.femail.value;
        let content = form.fcontent.value; 

        
        if(!name)
            throw new Error("Error: El campo de nombre es obligatorio");
        if(!email)
            throw new Error("Error: El campo de e-mail es obligatorio");
        if (!emailReg.test(email))
            throw new Error("El email no tiene un formato correcto");
        if(!content)
            throw new Error("Error: Es necesario que tenga algún contenido");
        
        form.reset();
    }
    catch(error){
        console.error("Error de validación: ", error.message);
        errorM.textContent = error.message;
    }
}





