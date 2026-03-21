import React from 'react';
import ReactDOM from 'react-dom/client';
import { RegisterForm } from './ui/auth-form';
import '../index.css';

const el = document.getElementById('react-register-form');
if (el) {
  const actionUrl = el.dataset.action || 'register.php';
  const errorMessage = el.dataset.error || '';
  ReactDOM.createRoot(el).render(
    <React.StrictMode>
      <RegisterForm actionUrl={actionUrl} errorMessage={errorMessage} />
    </React.StrictMode>
  );
}
