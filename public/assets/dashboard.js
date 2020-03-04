var graficoData = {};
var relevant = {};

function getRelevant(entity, dados) {
    for(let i in dicionarios[entity]) {
        if(dicionarios[entity][i].format === relevant[0])
            return dados[dicionarios[entity][i].column] || "não definido";
    }
}

function getGraficoData(grafico) {
    if(typeof graficoData[grafico.entity] !== "undefined") {
        if (dicionarios[grafico.entity][grafico.x].key === "relation") {
            for (let e in graficoData[grafico.entity]) {
                let id = parseInt(graficoData[grafico.entity][e][grafico.x]);
                if(!isNaN(id)) {
                    let relation = dicionarios[grafico.entity][grafico.x].relation;
                    if (typeof graficoData[relation] === "undefined" || typeof graficoData[relation][id] === "undefined") {
                        return db.exeRead(relation, id).then(rel => {
                            if(typeof graficoData[relation] === "undefined")
                                graficoData[relation] = {};

                            graficoData[relation][id] = rel;
                            graficoData[grafico.entity][e][grafico.x] = getRelevant(relation, graficoData[relation][id]);
                            return getGraficoData(grafico);
                        });
                    } else {
                        graficoData[grafico.entity][e][grafico.x] = getRelevant(relation, graficoData[relation][id]);
                    }
                }
            }
        }

        return Promise.all([]);

    } else {
        /**
         * Caso não tenha os dados desta entidade na variável 'graficoData'
         * busca os dados, adiciona na 'graficoData' e retorna novamente para esta função.
         */
        return db.exeRead(grafico.entity).then(dados => {
            graficoData[grafico.entity] = dados;
            return getGraficoData(grafico);
        })
    }
}

function showGrafico(graficos, i) {
    if(typeof graficos[i] === "object") {
        let grafico = graficos[i];
        return getGraficoData(grafico).then(() => {
            let size = grafico.size === "100%" ? "12" : (grafico.size === "50%" ? "6" : "4");
            let $div = $("<div id='grafico-" + grafico.id + "' class='col s" + size + "'></div>").appendTo(".dashboard-panel");
            let g = new Grafico($div[0]);
            g.setX(grafico.x);
            g.setY(grafico.y);
            g.setType(grafico.type);
            g.setTitle(ucFirst(grafico.entity));
            g.setOperacao(grafico.operacao);
            g.setMaximo(grafico.maximo);
            g.setLabelY(grafico.labely);
            g.setLabelX(grafico.labelx);
            g.toogleLegendShow();
            g.setData(graficoData[grafico.entity]);
            g.show();

            return showGrafico(graficos, (i+1))
        });
    }
}

$(function () {
    getGraficos().then(graficos => {
        dbLocal.exeRead("__relevant", 1).then(relev => {
            relevant = relev;
            showGrafico(graficos, 0);
        });
    });
});
