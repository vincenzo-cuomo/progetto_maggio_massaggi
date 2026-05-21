import { useState } from "react"
import { Link, useFetcher, useNavigate } from "react-router-dom"

import styles from './login.module.css'


function Input({ name, label, value, change }) {
    return (
        <div className={styles.labelWrapper}>
            <label htmlFor={name} className={styles.labelForm}>{label}</label>
            <input name={name} className={styles.formInputControl} onChange={change} value={value} id={name} type={name} autoComplete="off" />
        </div>
    )
}

export default function LoginForm() {
    const fetcher = useFetcher()
    const [data, setFormData] = useState({})
    const [error, hasError] = useState(false)
    const navigate = useNavigate()


    function ValueChange(e) {
        const { name, value } = e.target
        setFormData(prev => ({ ...prev, [name]: value }))

    }

    function handleSubmit() {
        if (fetcher.data){

        }
    }

    return (
        <div className={styles.box}>
            <div className={styles.wrapper}>
                <fetcher.Form method="POST" action="/login" className={styles.form}>
                    <Input name="email" label="Email" value={data.email} change={ValueChange}></Input>
                    <Input name="password" label="Password" value={data.password} change={ValueChange}></Input>
                    <input type="submit" value="Submit" onSubmit={handleSubmit} />
                    <ul>
                        <li><Link to="/createAccount"></Link></li>
                        <li><Link to="/forgotPassword"></Link></li>
                    </ul>
                </fetcher.Form>
            </div>
        </div >
    )
}