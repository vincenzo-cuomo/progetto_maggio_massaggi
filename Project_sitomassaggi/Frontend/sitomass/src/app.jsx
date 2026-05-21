import { createBrowserRouter, redirect } from "react-router-dom"
import Login from "./Login/login"
import Homepage from "./Homepage/homepage"
import MassagePage from "./Massage/massagepage"
let server = "http://localhost:8080"

const router = createBrowserRouter([
  {
    path: "/login",
    element: <Login />,
    action:
      async function ({ request }) {
        try {
          const formData = await request.formData()
          const dataForm = Object.fromEntries(formData)
          const data = await fetch(server + "/api/users/login", { method: "POST", headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(dataForm) })
          const res = await data.json()
          const token = data.headers.get("Authorization")?.replace("Bearer ", "");
          localStorage.setItem("JWT", token)
          redirect("/")
          console.log(res)
        } catch (error) { 
          console.log("problema")
        }

       
      }
  },
  {
    path: "/",
    element: <Homepage />
  },
  {
    path: "/massages/:massageID",
    loader: async ({ params }) => {
      let { massageID } = params
      const data = await fetch(server + "/api/massages/" + massageID, { method: "GET" })
      const res = await data.json()
      if (res.success == true) {
        const massage = res.Massage
        return massage
      }
    },
    element: <MassagePage />
  }

])

export default router