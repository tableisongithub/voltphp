import React from 'react'
import ReactDOM from 'react-dom/client'
import './index.css'
import './App.css'
import { createBrowserRouter, createRoutesFromElements, Link, Route, RouterProvider, useRouteError } from 'react-router-dom'
import { Suspense } from 'react'
import { StoreProvider } from 'easy-peasy'
import { store } from './state'
import Home from './test'

const ErrorBoundary = () => {
  const error = useRouteError();
  console.log(error)
  return (
    <div className="App">
      <header className="App-header">
        <p>
          Dont care
        </p>
      </header>
    </div>
  )
}

const router = createBrowserRouter(
  createRoutesFromElements(
    <>
      <Route path='/' errorElement={<ErrorBoundary />}>
        <Route path='/' element={<Link to='/id'>Home</Link>} />
        <Route
          path='/id'
          element={<Home />}
        />
      </Route>
    </>
  )
);


ReactDOM.createRoot(document.getElementById("root")!).render(
  <StoreProvider store={store}>
    <RouterProvider router={router} fallbackElement={<>Test</>} />
  </StoreProvider>
)
