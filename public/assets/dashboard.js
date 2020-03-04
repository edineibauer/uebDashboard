$(function () {
    getGraficos().then(graficos => {
        for(let i in graficos) {
            let grafico = graficos[i];
            dbLocal.exeRead(grafico.entity).then(dados => {
                let $div = $("<div id='grafico-" + grafico.id + "' class='col s6'></div>").appendTo(".dashboard-panel");
                let g = new Grafico($div[0]);
                g.setX(grafico.x);
                g.setY(grafico.y);
                g.setType(grafico.type);
                g.setTitle(ucFirst(grafico.entity));
                g.setOperacao(grafico.operacao);

                g.setData(dados);
                g.show();
            });
        }
    });
});