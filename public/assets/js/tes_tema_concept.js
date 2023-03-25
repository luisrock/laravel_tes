document.addEventListener("DOMContentLoaded", function() {

    const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    //generating concept
    const conceptCreateButton = document.querySelector('#concept-create');
    if(conceptCreateButton) {
        const conceptSaveRoute = conceptCreateButton.getAttribute('data-concept-save-route');
        const conceptGenerateRoute = conceptCreateButton.getAttribute('data-concept-generate-route');
        conceptCreateButton.addEventListener('click', async function () {
            console.log('clicked')
            const conceptCreateButton = document.querySelector('#concept-create');
            const label = conceptCreateButton.getAttribute('data-concept-label'); 
            const conceptId = conceptCreateButton.getAttribute('data-concept-id');
            setLoading(true);
            const conceptUserPrompt = document.querySelector('#concept-user-prompt').value.trim();
            const conceptSystemPrompt = document.querySelector('#concept-system-prompt').value.trim();
            const prompt = [
                {"role": "system", "content": conceptSystemPrompt},
                {"role": "user", "content": conceptUserPrompt},
            ]
            //get input[name=gpt-model] value
            const gptModel = document.querySelector('input[name="gpt-model"]:checked').value;
            try {
                console.log("Fazendo a requisição ao " + gptModel + "...")
                const generateConceptResponse = await fetch(conceptGenerateRoute, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrf_token
                    },
                    body: JSON.stringify({
                        messages: prompt,
                        model: gptModel,
                    })
                });
                
                const generateConceptData = await generateConceptResponse.json();
                const concept = generateConceptData.concept;

                console.log("Salvando a resposta...")

                // Salvar conceito no banco de dados
                const saveConceptResponse = await fetch(conceptSaveRoute, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrf_token
                    },
                    body: JSON.stringify({
                        concept: concept,
                        concept_id: conceptId
                    })
                });

                const saveConceptData = await saveConceptResponse.json();

                if (saveConceptData.success) {
                    alert('Conceito salvo com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao salvar conceito: ' + saveConceptData.message);
                }
            } catch (error) {
                console.log(error);
                alert('Erro: ' + error.message);
            } finally {
                setLoading(false);
            }
        });
    }
    //generating concept - END 


    //concept text toggle
    const description = document.getElementById("conceito");
    //if not found, return
    if (!description) {
        return;
    }
    const originalText = description.innerHTML;
    const truncatedText = originalText.substring(0, 400);
  
    function toggleText(show = false) {
      const showMoreLink = document.getElementById("showMore");
      if (showMoreLink || show) {
        description.innerHTML = originalText + ' <a id="showLess" href="#">[-]</a>';
      } else {
        description.innerHTML = truncatedText + ' <a id="showMore" href="#">...[+]</a>';
      }
      const newLink = document.getElementById("showMore") || document.getElementById("showLess");
      newLink.addEventListener("click", function(event) {
        event.preventDefault();
        toggleText();
      });
      const openConcept = document.getElementById("open-concept");
    }
    const openConcept = document.getElementById("open-concept");
    openConcept.addEventListener("click", function(event) {
      event.preventDefault();
      toggleText();
    });

    toggleText();
    //concept text toggle - END
  

    //concept buttons
    const validateButton = document.getElementById('#concept-validate');
    const editButton = document.getElementById('#concept-edit');
    const removeButton = document.getElementById('#concept-remove');
  
    if (validateButton) {
        validateButton.addEventListener('click', function() {
            const conceptId = this.getAttribute('data-concept-id');
            validateConcept(conceptId);
        });
    }
  
    if (editButton) {
        editButton.addEventListener('click', function() {
            toggleText(true)
            const conceptId = this.getAttribute('data-concept-id');
            openEditConceptModal(conceptId);
        });
    }
  
    if (removeButton) {
        removeButton.addEventListener('click', function() {
            const conceptId = this.getAttribute('data-concept-id');
            openRemoveConceptModal(conceptId);
        });
    }
});
// document.addEventListener - END


//FUNCTIONS
function setLoading(loading) {
    const loadingElement = document.querySelector('#loading');
    const originalContentElement = document.querySelector('#original-content');

    if (loading) {
        loadingElement.style.display = 'inline';
        originalContentElement.style.display = 'none';
    } else {
        loadingElement.style.display = 'none';
        originalContentElement.style.display = 'inline';
    }
}
  
function validateConcept(conceptId) {
  const xhr = new XMLHttpRequest();
  const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  xhr.open('POST', '/validate-concept', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);

  xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);

          if (response.success) {
              alert('Conceito validado com sucesso!');
              window.location.reload();
          } else {
              alert('Ocorreu um erro ao validar o conceito. Por favor, tente novamente.');
          }
      }
  };

  xhr.send(`concept_id=${conceptId}`);
}

function removeConcept(conceptId) {
    const xhr = new XMLHttpRequest();
    const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    xhr.open('POST', '/remove-concept', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);
  
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
  
            if (response.success) {
                alert('Conceito removido com sucesso!');
                window.location.reload();
            } else {
                alert('Ocorreu um erro ao remover o conceito. Por favor, tente novamente.');
            }
        }
    };
  
    xhr.send(`concept_id=${conceptId}`);
}

function editConcept(conceptId, newText) {
    const xhr = new XMLHttpRequest();
    const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    xhr.open('POST', '/edit-concept', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);
  
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
  
            if (response.success) {
                alert('Conceito editado com sucesso!');
                document.getElementById('conceito').textContent = newText;
                $('#editConceptModal').modal('hide');
            } else {
                alert('Ocorreu um erro ao editar o conceito. Por favor, tente novamente.');
            }
        }
    };
  
    xhr.send(`concept_id=${conceptId}&new_text=${encodeURIComponent(newText)}`);
}

function openEditConceptModal(conceptId) {
  let conceptText = document.getElementById('conceito').textContent;

  //replace the '[-]' with ''
    conceptText = conceptText.replace('[-]', '');

  const textarea = document.getElementById('editConceptTextarea');
  textarea.value = conceptText;

  $('#editConceptModal').modal('show');

  const saveEditConceptButton = document.getElementById('saveEditConcept');
  saveEditConceptButton.setAttribute('data-concept-id', conceptId);
  saveEditConceptButton.addEventListener('click', function() {
    editConcept(conceptId, textarea.value);
  });
}

function openRemoveConceptModal(conceptId) {
  $('#removeConceptModal').modal('show');

  const confirmRemoveConceptButton = document.getElementById('confirmRemoveConcept');
  confirmRemoveConceptButton.setAttribute('data-concept-id', conceptId);
  confirmRemoveConceptButton.addEventListener('click', function() {
      removeConcept(conceptId);
  });
}
//FUNCTIONS - END
