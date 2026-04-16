import { createBrowserRouter, redirect } from "react-router-dom"
import Login from "./login"
import Homepage from "./homepage"

const router = createBrowserRouter([
  {
    path: "/login",
    element: <Login />,
    action:
      async function ({ request }) {
        const formData = await request.formData()
        const dataForm = Object.fromEntries(formData)
        const data = await fetch("http://localhost:8080/api/users/login", { method: "POST", headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dataForm) })
        const res = await data.json()
        const token = data.headers.get("Authorization")?.replace("Bearer ", "");
        localStorage.setItem("JWT", token)
        console.log(res)
        if(res.success) {
        return redirect("/");
        }
      }
    },
{
  path: "/",
    element: <Homepage />
}
])

export default router