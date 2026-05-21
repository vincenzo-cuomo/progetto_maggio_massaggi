import { useEffect, useState } from 'react'
import styles from './homepage.module.css'
import { Link, Navigate, useNavigate } from 'react-router-dom'
import ErrorComponent from '../snippets/errorComponent/errorcomponent'

function MassageCard({ name, description, img, clickFunc }) {
    return (
        <div className={styles.massageCard} onClick={clickFunc}>
            <div className={styles.massageName}>{name}</div>
            <div className={styles.massageImage}><img src={img} alt="" /><p>{description}</p></div>
        </div>
    )
}


export default function Homepage() {
    const [isLogged, setIsLogged] = useState(false)
    const [username, setUsername] = useState("")
    const [massages, setMassage] = useState({})
    const [error, hasError] = useState(true)
    const navigate = useNavigate()


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

    async function getAllMassages() {
        try {
            const dbFetch = await fetch("http://localhost:8080/api/massages", { method: "GET" }).then(function (response) {
                if (!response.ok) {
                    hasError(true)
                    return false
                }
                hasError(false)
                return response.json()
            })
            return dbFetch
        } catch {
            console.log("Fetch fallita");
            hasError(true);
            return;
        }
    }
    useEffect(() => {
        async function fetchMassages() {
            const res = await getAllMassages()
            res ? setMassage(res.massages) : hasError(true)
        }
        fetchMassages()
    }, [])

    function massageNavigator(massageID) {
        navigate(`/massages/${massageID}`)
    }


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
                    {!error && Object.values(massages).map(massageName =>  //con obj value test1 : {ID: '1', Nome: 'test1', Descrizione: 'descriptiontest', URLImage: 'http://example.com'},senza obj values massages : {maasageName : {ID: '1', Nome: 'test1', Descrizione: 'descriptiontest', URLImage: 'http://example.com'}}
                        <MassageCard key={massageName.ID} name={massageName.Nome} description={massageName.Descrizione} img={massageName.URLImage} clickFunc={() => massageNavigator(massageName.ID)} ></MassageCard> //In click func ho dovuto usare una arrowfunc per RIFERIRMI alla funzione massageNavigator piuttosto che chiamarla direttamente in modo che essa avvenga quando l'utente clicca il massaggio e non instantaneamente (per colpa di javascript), questo si deve fare solo quando si passano delle variabili (es. miaFunzione() ) qui aggiungi delle parentesi che la chiamano direttamente, con miaFunzione invece non ci sono le parentesi e quindi si passa solo il riferimento alla funzione e non si esegue direttamente
                    )}
                    
                </div>
                {error && <ErrorComponent></ErrorComponent> }
            </div >
        </>
    )

} 