import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';
import ClientForm from './ClientForm';

export default function Edit({
    client,
    clientTypes,
    partnerOptions,
    managerOptions,
    companyStatuses,
    sicCodes,
    company,
    extended,
    extendedLookups,
}) {
    return (
        <AuthenticatedLayout header="Edit client">
            <Head title={`Edit · ${client.name}`} />
            <ClientForm
                mode="edit"
                client={client}
                clientTypes={clientTypes}
                partnerOptions={partnerOptions}
                managerOptions={managerOptions}
                companyStatuses={companyStatuses}
                sicCodes={sicCodes}
                company={company}
                extended={extended}
                extendedLookups={extendedLookups}
            />
        </AuthenticatedLayout>
    );
}
