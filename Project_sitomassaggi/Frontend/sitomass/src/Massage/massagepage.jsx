import { useLoaderData } from "react-router-dom"
import styles from "./massagepage.module.css"


export default function MassagePage() {
    const massage = useLoaderData()
    console.log(massage)
    const Name = massage.Nome
    const Description = massage.Descrizione
    const URLImage = massage.URLImage
    const DurMed = massage.DurMed
    const costo = massage.Costo
    return (
        <>
            <header className={styles.header}>
                <div className={styles.massageName}>Massaggio {Name}</div>
                <div className={styles.massageInfo}>
                    <div className={styles.massageDurMed}><div>DURATA MEDIA<div>{DurMed}min</div></div></div>
                    <div className={styles.massageCost}><div>COSTO</div><div>{costo}€</div></div>
                </div>
            </header>
            <section className={styles.massageInsights}>
                <img className={styles.massageImage} src={URLImage} alt="" />
                <aside className={styles.massageDescription}>
                    <div className={styles.massageDescriptionWrapper}> 
                        <p>{Description}</p>
                        <button className={styles.massageSchedule}>PRENOTA</button>
                    </div>
                </aside>
            </section>
        </>
    )
}
