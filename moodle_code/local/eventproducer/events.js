document.addEventListener('DOMContentLoaded', function () {
    const eventForm = document.getElementById('eventForm');
    const eventsList = document.getElementById('eventsList');

    // Leer eventos al cargar la página
    fetchEvents();

    // Crear o actualizar un evento
    eventForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(eventForm);
        const action = formData.get('id') ? 'update' : 'create';

        fetch(`/local/eventproducer/manage_event.php?action=${action}`, {
            method: 'POST',
            body: new URLSearchParams(formData),
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    fetchEvents();
                    eventForm.reset();
                } else {
                    alert(data.message);
                }
            });
    });

    // Leer eventos
    function fetchEvents() {
        fetch('/local/eventproducer/manage_event.php?action=read')
            .then(response => response.json())
            .then(events => {
                eventsList.innerHTML = '';
                events.forEach(event => {
                    const listItem = document.createElement('li');
                    listItem.textContent = `${event.name} - ${event.type}`;
                    listItem.dataset.id = event.id;
                    listItem.addEventListener('click', () => loadEvent(event.id));
                    eventsList.appendChild(listItem);
                });
            });
    }

    // Cargar un evento en el formulario para editar
    function loadEvent(id) {
        fetch(`/local/eventproducer/manage_event.php?action=read&id=${id}`)
            .then(response => response.json())
            .then(event => {
                eventForm.querySelector('[name="id"]').value = event.id;
                eventForm.querySelector('[name="name"]').value = event.name;
                eventForm.querySelector('[name="description"]').value = event.description;
                eventForm.querySelector('[name="type"]').value = event.type;
                eventForm.querySelector('[name="status"]').value = event.status;
            });
    }

    // Eliminar un evento
    document.getElementById('deleteButton').addEventListener('click', function () {
        const id = eventForm.querySelector('[name="id"]').value;
        if (id && confirm('Are you sure you want to delete this event?')) {
            fetch(`/local/eventproducer/manage_event.php?action=delete&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchEvents();
                        eventForm.reset();
                    } else {
                        alert(data.message);
                    }
                });
        }
    });
});
