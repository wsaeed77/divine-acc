import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';
import ClientForm from './ClientForm';

export default function Create({
    clientTypes,
    partnerOptions,
    managerOptions,
    companyStatuses,
    sicCodes,
    company,
    extended,
    extendedLookups,
    emptyExtendedTemplate,
    selfAssessmentTypeId,
}) {
    return (
        <AuthenticatedLayout header="New client">
            <Head title="New client" />
            <ClientForm
                mode="create"
                client={null}
                clientTypes={clientTypes}
                partnerOptions={partnerOptions}
                managerOptions={managerOptions}
                companyStatuses={companyStatuses}
                sicCodes={sicCodes}
                company={company}
                extended={extended}
                extendedLookups={extendedLookups}
                emptyExtendedTemplate={emptyExtendedTemplate}
                selfAssessmentTypeId={selfAssessmentTypeId}
            />
        </AuthenticatedLayout>
    );
}
