document.querySelectorAll('.project-button').forEach(button => {
    button.addEventListener('click', (e) => {
      const action = e.target.dataset.action;
      const project = e.target.dataset.project;
      postToProjects(action, project);
    });
  });
  

function postToProjects(action, project) {
    toggleImportAnimation(true);
    StatusModal.clear(action);
    StatusModal.setSpinner(true);
    StatusModal.show(action, ["[" + action + "] This takes awhile."]);
  
    fetch("/ajax/project_action.php", {
      method: "POST",
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action, project })
    })
    .then(res => res.text())
    .then(text => {
      // StatusModal.setSpinner(false);
      text.split("\n").forEach(line => StatusModal.append(line));
    })
    .catch(err => {
      // StatusModal.setSpinner(false);
      StatusModal.append("❌ Error: " + err.message);
    })
    .finally(() => {
      toggleImportAnimation(false);
    });
  }
  

function deployProject(name)   { postToProjects('deployProject', name); }
function backupProject(name)   { postToProjects('backupProject', name); }
function restoreProject(name)  { postToProjects('restoreProject', name); }
function stageProject(name)       { postToProjects('stageProject', name); }
function importProject(name)      { postToProjects('importProject', name); }
function configureProject(name)  { postToProjects('configureProject', name); }

function showSpinnerOverlay(show = true) {
    const overlay = document.getElementById("spinner-overlay");
    if (overlay) {
      overlay.classList.toggle("hidden", !show);
    }
  }

  function toggleImportAnimation(show) {
    const anim = document.getElementById('import-animation');
    if (!anim) return;
    anim.classList.toggle('hidden', !show);
}


 