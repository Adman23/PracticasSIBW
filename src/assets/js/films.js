
//----------------------------------------------------------------------------------
// Event listener al cargar la página
document.addEventListener('DOMContentLoaded', function(){
    const formSearch = document.getElementsByClassName("search-form")[0] ?? null;
    const search = formSearch ? formSearch.querySelector("input[name='q']") : null;
    
    // CASO 1: Quieres buscar todas las películas con un parámetro
    if (formSearch){
        formSearch.addEventListener("submit", function(event){
            // Realiza la petición ajax y coloca el resultado
            event.preventDefault();
            getFilmsAjax(currentPage);
        })
    }

    // CASO 2: Vas visualizando los X primeros resultados como desplegable
    if (search){
        search.addEventListener("keyup", function() {
            // Realizará la petición ajax y luego mostrará los resultados como
            // un desplegable del buscador, (mostrando los 10 primeros)
            lastQueryValue = search.textContent;
            getFilmsAjax("glance");   
        });
    }

    // CASO INICIAL: Simplemente tienes todas las películas en el listado
    // Realiza la petición ajax y coloca el resultado
    getFilmsAjax(currentPage);
});
//----------------------------------------------------------------------------------


//----------------------------------------------------------------------------------
// Hay que tener en cuenta los valores iniciales relevantes
//    -> Buscamos el indicador de que sea el listado de películas o la portada
//        -> Se colocará como variable global "currentPage" siendo las opciones
//           'portada' o 'listado'
//----------------------------------------------------------------------------------




//----------------------------------------------------------------------------------
// Función que utiliza fetch para conectarse al php y conseguir toda la información
// El type será 'portada', 'listado' o 'glance y cada uno acabará llamando a la función
// correspondiente para que construya los comentarios
function getFilmsAjax(type){
    const formDataSearch = new FormData(document.getElementsByClassName("search-form")[0]) ?? {};
    
    fetch("/ajax_peliculas.php", {
        body: formDataSearch,
        method: "POST",
    })
    .then(response => response.json())
    .then(response => {
        processFilms(response, type);
    })
    .catch(error => {
        console.log(error);
    });
}
//----------------------------------------------------------------------------------




//----------------------------------------------------------------------------------
// Función general
function processFilms(response, type){
    let films = response; // Ya se ha hecho response = response.json()
    let filmList = document.getElementsByClassName("film-list")[0];
    
    // Esto es solo para el glance
    let list = document.createElement("ul");


    if (type != "glance") {
        const addButtonContainer = document.getElementById("add-button-container");
        filmList.innerHTML = "";
        if (addButtonContainer)
            filmList.appendChild(addButtonContainer);
    }
    else{    
        const formSearch = document.getElementsByClassName("search-form")[0] ?? null;
        const search = formSearch ? formSearch.querySelector("input[name='q']") : null;
        
        if (search.value === ""){
            films = [];
        }
        list.classList.add("only-results");
        
        let searchBox = document.getElementsByClassName("search-box")[0];
        const lastChild = searchBox.lastElementChild;

        if (lastChild && lastChild.tagName.toLowerCase() !== "input") {
            lastChild.remove();
        }
        searchBox.append(list);
    }

    if (films.length != 0){
        if (type === "portada"){
            for (let i = 0; i < films.length; i++) {
                const li = createFilmCover(films[i]);
                filmList.appendChild(li);
            }   
        }
        else if (type === "listado"){
            for (let i = 0; i < films.length; i++) {
                const li = createFilmElementList(films[i]);
                filmList.appendChild(li);
            }   
        }
        else if (type === "glance"){ // Muestra solo las 10 más recientes
            for (let i = 0; i < films.length && i < 10; i++) {
                const li = createFilmGlance(films[i]);
                list.appendChild(li);
            }   
            let searchBox = document.getElementsByClassName("search-box")[0];
            searchBox.append(list);
        }
    }
}

// Se usa en la portada
function createFilmCover(film){
    let li = document.createElement("li");
    li.classList.add("film");

    // Creamos los elementos que contienen la carátula
    let link  = document.createElement("a");
    let cover = document.createElement("img");
    let name  = document.createElement("p");

    // Añadimos las clases para el aspecto
    cover.classList.add("cover");
    name.classList.add("film-name");

    // Añadimos los atributos necesarios
    link.setAttribute("href", `/pelicula/${film.id}`);
    cover.setAttribute("src", `data:image/*;base64,${film.cover}`);
    cover.setAttribute("alt", `pelicula llamada ${film.name}`);

    // Añadimos el contenido que haga falta
    name.textContent = film.name;

    // Lo juntamos todo y lo devolvemos
    link.append(cover);
    link.append(name);
    li.append(link);

    return li;
}

// Se usa en el listado de películas
function createFilmElementList(film){
    let li = document.createElement("li");
    li.classList.add("list-row");

    // Creamos los elementos que lo contienen
    let id = document.createElement("span");
    let name = document.createElement("span");
    let filmLink = document.createElement("a");
    let genre = document.createElement("span");
    let directors = document.createElement("span");
    let actors = document.createElement("span");
    
    // Asignamos las clases 
    id.classList.add("list-cell");
    name.classList.add("list-cell");
    genre.classList.add("list-cell");
    directors.classList.add("list-cell");
    actors.classList.add("list-cell");

    // Asignamos los atributos relevantes
    filmLink.setAttribute("href", `/pelicula/${film.id}`);

    // Asignamos el contenido de cada bloque
    id.textContent = film.id;
    filmLink.textContent = film.name;
    genre.textContent = film.genre;
    directors.textContent = film.directors;
    actors.textContent = film.actors;
    
    // Construimos el elemento y lo devolvemos
    name.append(filmLink);
    li.append(id);
    li.append(name);
    li.append(genre);
    li.append(directors);
    li.append(actors);

    return li
}


// Se usa en ambas páginas
function createFilmGlance(film){
    let li = document.createElement("li");
    li.classList.add("result");

    // Creamos los elementos
    let link = document.createElement("a");

    // Añadimos atributos
    link.setAttribute("href", `/pelicula/${film.id}`);

    // Añadimos contenido
    link.textContent = film.name;

    // Creamos el elemento
    li.append(link);

    return li;
}
//----------------------------------------------------------------------------------






