
let burgerOpen = false;

document.addEventListener("DOMContentLoaded", main);

function main() {
  document.querySelector("#nav_burger").addEventListener("click", toggleMenu);
}

function toggleMenu() {
  let footerE = document.querySelector("footer");
  let ffe_mainE = document.querySelector("#ffe_main");
  let navMenuE = document.querySelector("#nav_menu");
  let burger_presenterE = document.querySelector("#burger_presenter");
  let burgerliE = document.querySelectorAll("#nav_menu ol li");

  if (burgerOpen == false) {
    burgerOpen = true;

    ffe_mainE.style.display = 'none';
    footerE.style.display = 'none';
    burger_presenterE.style.display = 'block';

    burgerliE.forEach(function (element) {
      element.firstChild.addEventListener("mouseover", function (event) {
        let background = this.getAttribute("data-bgimg-url");
        burger_presenterE.style.backgroundImage = "url('" + background + "')";
      });
      element.firstChild.addEventListener("mouseout", function (event) {
        burger_presenterE.style.backgroundImage = "none";
      });
    });
    navMenuE.className = "show_menu";
  } else {
    burgerOpen = false;
    ffe_mainE.style.display = 'block';
    footerE.style.display = 'block';
    navMenuE.className = "nav_standard";
    burger_presenterE.style.display = 'none';

  }

}

/*
function main() {
  // Find all entities on the contact page, and apply the random rotation effect
  let persons = document.querySelectorAll(".person .details");
  let min = -4;
  let max = 4;

  persons.forEach(person => {
    let random = Math.floor(Math.random() * (+max - +min)) + +min;
    person.style.transform = "rotate(" + random + "deg)";
  });
}

*/
