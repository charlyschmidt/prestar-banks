document.addEventListener('DOMContentLoaded', () => {


    if (!document.getElementById('transactions-body')) {
        return;
    }



    Echo.channel('dashboard')

        .listen('.transaction.created', (event) => {


            console.log(
                'Nuevo movimiento:',
                event
            );


            const transaction = event.transaction;
            const account = event.account;



            const tbody = document.getElementById(
                'transactions-body'
            );


            const isIncome =
                [
                    'income',
                    'transfer_in'
                ].includes(transaction.type);



            const row = `

<tr>

<td>
    Hoy
</td>


<td>

<div class="account-cell">

    ${account.logo
                    ?
                    `<img src="/storage/${account.logo}">`
                    :
                    `<div class="mini-logo">
            <i class="bi bi-bank"></i>
        </div>`
                }


    <span>
        ${account.name}
    </span>

</div>

</td>



<td>

${isIncome

                    ?
                    `
<span class="movement-income">

<i class="bi bi-arrow-up"></i>
Ingreso

</span>
`

                    :

                    `
<span class="movement-expense">

<i class="bi bi-arrow-down"></i>
Egreso

</span>
`

                }

</td>



<td>

${transaction.description ?? 'Sin descripción'}

</td>




<td class="text-end">


${isIncome

                    ?

                    `
<span class="amount-income">
+
$${Number(transaction.amount)
                        .toLocaleString('es-AR')}
</span>
`

                    :

                    `
<span class="amount-expense">
-
$${Number(transaction.amount)
                        .toLocaleString('es-AR')}
</span>
`

                }


</td>


<td>


</td>


</tr>


`;



            tbody.insertAdjacentHTML(
                'afterbegin',
                row
            );


        });


});