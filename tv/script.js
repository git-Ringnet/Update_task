const editButton=document.querySelector('#edit-button');
const editor=document.querySelector('#editor');
const nameInput=document.querySelector('#name-input');
const messageInput=document.querySelector('#message-input');
const displayName=document.querySelector('#display-name');
const displayMessage=document.querySelector('#display-message');

editButton.addEventListener('click',()=>{
  editor.hidden=!editor.hidden;
  editButton.textContent=editor.hidden?'Chỉnh nội dung':'Đóng chỉnh sửa';
});
nameInput.addEventListener('input',()=>{displayName.textContent=nameInput.value||' ';});
messageInput.addEventListener('input',()=>{displayMessage.textContent=messageInput.value||' ';});
editor.addEventListener('submit',event=>event.preventDefault());
