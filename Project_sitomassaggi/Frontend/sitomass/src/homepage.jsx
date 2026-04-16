import { useEffect, useState } from 'react'
import styles from './homepage.module.css'
import img from "./Images/womangettingamassage.webp"
import { Link } from 'react-router-dom'

function MassageCard({ name, description, img }) {

    const handleMouseEnter = () => {
        console.log("ciaoo")
    }

    return (
        <div className={styles.massageCard} onMouseEnter={handleMouseEnter}>
            <div className={styles.massageName}>{name}</div>
            <div className={styles.massageImage}><img src={img} alt="" /></div>
        </div>
    )
}


export default function Homepage() {
    const [isLogged, setIsLogged] = useState(false)
    const [username, setUsername] = useState("")
    const [a, seta] = useState("")


    async function checkDBJWT() {
        if (localStorage.getItem("JWT")) {
            try {
                const dbfetch = await fetch("http://localhost:8080/api/users/JWTvalidation", { method: "POST", headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token: localStorage.getItem("JWT") }) })
                const res = await dbfetch.json()
                if (dbfetch.status === 200) {
                    setUsername(res.username)
                    return true
                }
            } catch (error) {
                console.log("Impossibile estrarre dati")
            }

        } else { return false }
    } useEffect(() => {
        async function fecthData() {
            const response = await checkDBJWT()
            if (response) { setIsLogged(response) }
        }
        fecthData()
    }, [])

    return (
        <>
            <div className={styles.mainBG}>
                <header>
                    <ul className={styles.headerNav}>
                        <li className={styles.nameMassaggi}><div><p>Massaggi</p></div></li>
                        {!isLogged && (<><li className={styles.loginLi} ><div><Link to="/login"><p className={styles.loginLiP}>Accedi</p></Link></div></li>
                            <li className={styles.loginLi}><p className={styles.loginLiP}>Registrati</p></li></>)
                        }
                        {isLogged && (<li className={styles.user}><div><p>Rieccoti {username}!</p></div></li>)}
                        <li className={styles.nameHeader}><div><p>Maria Acciarino</p></div></li>
                    </ul>
                </header>
                <div className={styles.massageContainer}>
                    <MassageCard name="LINFODRENANTE" description="bim" img={img} />
                </div>
            </div >
        </>
    )

}