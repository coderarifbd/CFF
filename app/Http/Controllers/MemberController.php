<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Restrict create/update/delete to Admin or Super Admin
        $this->middleware(['role:Admin|Super Admin'])->only(['create','store','edit','update','destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Member::with('user');

        if (request('search')) {
            $search = trim(request('search'));
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }
        if (request('type')) {
            $query->where('member_type', request('type'));
        }

        $members = $query->latest()->paginate(15)->withQueryString();

        return view('members.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Create or reuse linked user by email
        $user = User::where('email', $data['email'])->first();
        if ($user) {
            // If an existing user is found (and validation allowed it), update name and password
            $user->update([
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
            ]);
            // Sync role to selected type
            $user->syncRoles();
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        }
        // Assign role based on member_type
        $role = match($data['member_type']){
            'admin' => 'Admin',
            'accountant' => 'Accountant',
            default => 'Member'
        };
        $user->assignRole($role);

        // Create member linked to user
        $member = Member::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'nid' => $data['nid'],
            'address' => $data['address'] ?? null,
            'profile_picture' => $data['profile_picture'] ?? null,
            'join_date' => $data['join_date'],
            'status' => $data['status'],
            'member_type' => $data['member_type'],
            'user_id' => $user->id,
        ]);

        return redirect()->route('members.index')->with('status', 'Member created successfully with user account');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        // Gather deposit totals and simple monthly breakdown for this member
        \App\Models\DepositItem::query(); // ensure class is autoloadable even if not referenced elsewhere

        $subscriptionSum = \App\Models\DepositItem::where('type','subscription')
            ->whereHas('receipt', fn($q) => $q->where('member_id', $member->id))
            ->sum('amount');

        $extraSum = \App\Models\DepositItem::where('type','extra')
            ->whereHas('receipt', fn($q) => $q->where('member_id', $member->id))
            ->sum('amount');

        $fineSum = \App\Models\DepositItem::where('type','fine')
            ->whereHas('receipt', fn($q) => $q->where('member_id', $member->id))
            ->sum('amount');

        $totalDeposit = (float) $subscriptionSum + (float) $extraSum; // exclude fines from total deposit

        // Placeholder for investments tied to a member (not modeled yet)
        $investPlusInterest = 0.0;

        // New rule: Member balance = ((Deposits + Interest) - Expenses) / Total Members
        $globalTotalDeposits = (float) \App\Models\DepositReceipt::sum('total_amount');
        $globalTotalInterest = (float) \App\Models\InvestmentInterest::sum('amount');
        $globalExpenses = (float) \App\Models\Cashbook::where('type','expense')->sum('amount');

        $globalEqualShareBase = $globalTotalDeposits + $globalTotalInterest - $globalExpenses;

        $totalMembers = max(1, (int) Member::count());
        $balance = $globalEqualShareBase / $totalMembers;

        // Monthly history from receipts (group by YYYY-MM)
        $receipts = \App\Models\DepositReceipt::with('items')
            ->where('member_id', $member->id)
            ->orderBy('date','desc')
            ->get();

        $monthly = [];
        foreach ($receipts as $r) {
            $key = $r->date->format('Y-m');
            $monthly[$key] = $monthly[$key] ?? ['month' => $r->date->format('F Y'), 'subscription' => 0, 'extra' => 0, 'fine' => 0];
            foreach ($r->items as $it) {
                if ($it->type === 'subscription') {
                    $monthly[$key]['subscription'] += (float) $it->amount;
                } elseif ($it->type === 'extra') {
                    $monthly[$key]['extra'] += (float) $it->amount;
                } elseif ($it->type === 'fine') {
                    $monthly[$key]['fine'] += (float) $it->amount;
                }
            }
        }

        // show latest 12 months rows
        $monthlyRows = collect($monthly)->sortKeysDesc()->take(12)->values()->all();

        return view('members.show', compact('member','totalDeposit','investPlusInterest','balance','monthlyRows','subscriptionSum','extraSum','fineSum','globalEqualShareBase','totalMembers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();

        if ($request->hasFile('profile_picture')) {
            if ($member->profile_picture) {
                Storage::disk('public')->delete($member->profile_picture);
            }
            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Update or link User and set role according to member_type
        $role = match($data['member_type']){
            'admin' => 'Admin',
            'accountant' => 'Accountant',
            default => 'Member'
        };

        $linkedUserId = $member->user_id;
        if ($member->user_id) {
            $user = User::find($member->user_id);
            if ($user) {
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);
                $user->syncRoles([$role]);
            }
        } else {
            // Try to link by email if a user exists
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                $user->update(['name' => $data['name']]);
                $user->syncRoles([$role]);
                $linkedUserId = $user->id; // link
            }
        }

        $member->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'nid' => $data['nid'],
            'address' => $data['address'] ?? null,
            'profile_picture' => $data['profile_picture'] ?? $member->profile_picture,
            'join_date' => $data['join_date'],
            'status' => $data['status'],
            'member_type' => $data['member_type'],
            'user_id' => $linkedUserId,
        ]);

        return redirect()->route('members.index')->with('status', 'Member updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        // Prevent self-delete if the member is linked to the currently authenticated user
        if ($member->user_id && auth()->id() === $member->user_id) {
            return back()->withErrors(['delete' => 'You cannot delete your own profile. Ask another Admin to perform this action.']);
        }

        $member->delete();
        return redirect()->route('members.index')->with('status', 'Member deleted');
    }

    /**
     * Admin-only password reset for a member's linked user.
     */
    public function resetPassword(Member $member, Request $request)
    {
        $request->validate([
            'new_password' => ['required','string','min:8','confirmed'],
        ]);

        if (!$member->user_id) {
            return back()->withErrors(['new_password' => 'No linked user account to reset password.']);
        }

        $user = User::findOrFail($member->user_id);
        $user->update(['password' => Hash::make($request->input('new_password'))]);

        return back()->with('status', 'Password has been reset for this member');
    }

    /**
     * Suspend a member account (Admin only)
     */
    public function suspend(Member $member)
    {
        $member->update(['status' => 'suspended']);
        return back()->with('status', 'Member suspended');
    }

    /**
     * Activate a member account (Admin only)
     */
    public function activate(Member $member)
    {
        $member->update(['status' => 'active']);
        return back()->with('status', 'Member activated');
    }
}
