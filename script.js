function insertBBCode(startTag, endTag) {
    const textarea = document.getElementById('detalle');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    // Obtener el texto seleccionado
    const selectedText = textarea.value.substring(start, end);

    // Insertar las etiquetas BBCode alrededor del texto seleccionado
    const newText = textarea.value.substring(0, start) +
        startTag + selectedText + endTag +
        textarea.value.substring(end);

    // Actualizar el contenido del textarea
    textarea.value = newText;

    // Volver a enfocar el textarea
    textarea.focus();
    textarea.selectionStart = start + startTag.length;
    textarea.selectionEnd = end + startTag.length;
}
