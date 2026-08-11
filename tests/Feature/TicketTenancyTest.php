<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTenancyTest extends TestCase
{
    use RefreshDatabase;

    private Organization $northwind;

    private Organization $contoso;

    private User $northwindMember;

    private User $northwindManager;

    private User $contosoMember;

    private User $contosoManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->northwind = Organization::factory()->create(['name' => 'Northwind Services']);
        $this->contoso = Organization::factory()->create(['name' => 'Contoso Operations']);

        $this->northwindMember = User::factory()->for($this->northwind)->create([
            'role' => User::ROLE_MEMBER,
        ]);
        $this->northwindManager = User::factory()->manager()->for($this->northwind)->create();
        $this->contosoMember = User::factory()->for($this->contoso)->create([
            'role' => User::ROLE_MEMBER,
        ]);
        $this->contosoManager = User::factory()->manager()->for($this->contoso)->create();
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get(route('tickets.index'))->assertRedirect(route('login'));
    }

    public function test_member_only_sees_tickets_from_their_organization(): void
    {
        $ownTicket = $this->createTicket($this->northwind, $this->northwindMember, [
            'title' => 'Northwind visible ticket',
        ]);
        $otherTicket = $this->createTicket($this->contoso, $this->contosoMember, [
            'title' => 'Contoso private ticket',
        ]);

        $this->actingAs($this->northwindMember)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($ownTicket->title)
            ->assertDontSee($otherTicket->title);
    }

    public function test_manager_only_sees_tickets_from_their_organization(): void
    {
        $ownTicket = $this->createTicket($this->northwind, $this->northwindManager, [
            'title' => 'Manager visible ticket',
        ]);
        $otherTicket = $this->createTicket($this->contoso, $this->contosoManager, [
            'title' => 'Other manager private ticket',
        ]);

        $this->actingAs($this->northwindManager)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($ownTicket->title)
            ->assertDontSee($otherTicket->title);
    }

    public function test_cross_tenant_ticket_detail_returns_not_found(): void
    {
        $ticket = $this->createTicket($this->contoso, $this->contosoMember);

        $this->actingAs($this->northwindMember)
            ->get(route('tickets.show', $ticket))
            ->assertNotFound();
    }

    public function test_member_creates_ticket_with_server_derived_ownership(): void
    {
        $response = $this->actingAs($this->northwindMember)->post(route('tickets.store'), [
            'title' => 'New tenant-owned ticket',
            'description' => 'The server must assign both ownership fields.',
            'priority' => Ticket::PRIORITY_HIGH,
            'organization_id' => $this->contoso->id,
            'created_by' => $this->contosoManager->id,
            'status' => Ticket::STATUS_CLOSED,
        ]);

        $ticket = Ticket::query()->where('title', 'New tenant-owned ticket')->firstOrFail();

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame($this->northwind->id, $ticket->organization_id);
        $this->assertSame($this->northwindMember->id, $ticket->created_by);
        $this->assertSame(Ticket::STATUS_OPEN, $ticket->status);
    }

    public function test_invalid_ticket_input_is_rejected(): void
    {
        $this->actingAs($this->northwindMember)
            ->post(route('tickets.store'), [
                'title' => '',
                'description' => '',
                'priority' => 'urgent',
            ])
            ->assertSessionHasErrors(['title', 'description', 'priority']);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_manager_can_update_status_for_own_tenant_ticket(): void
    {
        $ticket = $this->createTicket($this->northwind, $this->northwindMember);

        $this->actingAs($this->northwindManager)
            ->patch(route('tickets.status.update', $ticket), [
                'status' => Ticket::STATUS_CLOSED,
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => Ticket::STATUS_CLOSED,
        ]);
    }

    public function test_invalid_status_update_is_rejected(): void
    {
        $ticket = $this->createTicket($this->northwind, $this->northwindMember);

        $this->actingAs($this->northwindManager)
            ->patch(route('tickets.status.update', $ticket), [
                'status' => 'waiting_for_vendor',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => Ticket::STATUS_OPEN,
        ]);
    }

    public function test_member_cannot_update_ticket_status(): void
    {
        $ticket = $this->createTicket($this->northwind, $this->northwindMember);

        $this->actingAs($this->northwindMember)
            ->patch(route('tickets.status.update', $ticket), [
                'status' => Ticket::STATUS_CLOSED,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => Ticket::STATUS_OPEN,
        ]);
    }

    public function test_status_form_is_only_visible_to_managers(): void
    {
        $ticket = $this->createTicket($this->northwind, $this->northwindMember);

        $this->actingAs($this->northwindMember)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('Update status');

        $this->actingAs($this->northwindManager)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Update status');
    }

    public function test_manager_cannot_update_another_tenant_ticket(): void
    {
        $ticket = $this->createTicket($this->contoso, $this->contosoMember);

        $this->actingAs($this->northwindManager)
            ->patch(route('tickets.status.update', $ticket), [
                'status' => Ticket::STATUS_CLOSED,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => Ticket::STATUS_OPEN,
        ]);
    }

    public function test_search_stays_scoped_when_text_only_matches_another_tenant(): void
    {
        $this->createTicket($this->northwind, $this->northwindMember, [
            'title' => 'Routine local request',
        ]);
        $otherTicket = $this->createTicket($this->contoso, $this->contosoMember, [
            'title' => 'Confidential Acorn keyword',
            'description' => 'Acorn appears in another tenant.',
        ]);

        $this->actingAs($this->northwindMember)
            ->get(route('tickets.index', ['search' => 'ACORN']))
            ->assertOk()
            ->assertDontSee($otherTicket->title)
            ->assertSee('No tickets found');
    }

    public function test_combined_search_and_status_filter_returns_correct_results(): void
    {
        $matching = $this->createTicket($this->northwind, $this->northwindMember, [
            'title' => 'Printer paper jam',
            'status' => Ticket::STATUS_OPEN,
        ]);
        $wrongStatus = $this->createTicket($this->northwind, $this->northwindMember, [
            'title' => 'Printer toner request',
            'status' => Ticket::STATUS_CLOSED,
        ]);
        $wrongSearch = $this->createTicket($this->northwind, $this->northwindMember, [
            'title' => 'Laptop issue',
            'status' => Ticket::STATUS_OPEN,
        ]);

        $this->actingAs($this->northwindMember)
            ->get(route('tickets.index', [
                'search' => 'printer',
                'status' => Ticket::STATUS_OPEN,
            ]))
            ->assertOk()
            ->assertSee($matching->title)
            ->assertDontSee($wrongStatus->title)
            ->assertDontSee($wrongSearch->title);
    }

    public function test_pagination_preserves_active_filter_parameters(): void
    {
        Ticket::factory()
            ->count(11)
            ->for($this->northwind)
            ->for($this->northwindMember, 'creator')
            ->create([
                'title' => 'Needle ticket',
                'description' => 'Needle filter result',
                'status' => Ticket::STATUS_OPEN,
            ]);

        $this->actingAs($this->northwindMember)
            ->get(route('tickets.index', [
                'search' => 'needle',
                'status' => Ticket::STATUS_OPEN,
            ]))
            ->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->count() === 10)
            ->assertSee('search=needle', false)
            ->assertSee('status=open', false);
    }

    public function test_test_suite_is_locked_to_dedicated_database(): void
    {
        $this->assertSame('interview_test_testing', config('database.connections.mysql.database'));
        $this->assertNotSame('interview_test', config('database.connections.mysql.database'));
    }

    private function createTicket(
        Organization $organization,
        User $creator,
        array $attributes = []
    ): Ticket {
        return Ticket::factory()
            ->for($organization)
            ->for($creator, 'creator')
            ->create(array_merge([
                'status' => Ticket::STATUS_OPEN,
                'priority' => Ticket::PRIORITY_NORMAL,
            ], $attributes));
    }
}
