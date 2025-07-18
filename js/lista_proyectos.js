// function searchProjects() {
    // const input = document.getElementById("searchInput").value;

    // Realizar una solicitud AJAX al servidor
    // fetch(`buscar_proyectos.php?query=${encodeURIComponent(input)}`)
    //     .then(response => response.json())
     //    .then(data => {
    //         const projectList = document.getElementById("projectList");
     //        projectList.innerHTML = ""; // Limpiar resultados anteriores
// 
    //         // Añadir los proyectos al listado
    //         data.forEach(project => {
    //             const li = document.createElement("li");
     //            li.textContent = project;
     //            li.classList.add("project-item");
    //             projectList.appendChild(li);
     //        });
     //    })
    //     .catch(error => console.error("Error:", error));
// }

function editProject(projectId) {
    // Redirigir a la página de edición
    window.location.href = `editar_proyecto.php?id_proyecto=${projectId}`;
}

function deleteProject(projectId) {
    // Confirmar la eliminación
    if (confirm("¿Estás seguro de que deseas eliminar este proyecto?")) {
        fetch(`eliminar_proyecto.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `id_proyecto=${projectId}`
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload(); // Recargar la página después de eliminar
        })
        .catch(error => console.error("Error al eliminar el proyecto:", error));
    }
}

function confirmDelete(idProyecto) {
    if (confirm("¿Estás seguro de que deseas eliminar este proyecto? Esta acción no se puede deshacer.")) {
        window.location.href = `eliminar_proyecto.php?id=${idProyecto}`;
    }
}
